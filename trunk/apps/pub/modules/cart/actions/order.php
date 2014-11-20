<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    if ( !($this->getUser()->getTransaction() instanceof Transaction) )
      return $this->redirect('event/index');
    
    // if it is a pay-only process
    $tid = intval($request->getParameter('transaction_id')).'' === ''.$request->getParameter('transaction_id','')
      ? $request->getParameter('transaction_id')
      : false;
    $this->transaction = $tid ? Doctrine::getTable('Transaction')->find($tid) : $this->getUser()->getTransaction();
    if ( $this->transaction->contact_id != $this->getUser()->getTransaction()->contact_id )
      $this->transaction = $this->getUser()->getTransaction();
    
    // harden data
    if ( $this->transaction->id == $this->getUser()->getTransactionId() )
      $this->getContext()->getConfiguration()->hardenIntegrity();
    
    try { $this->form = new ContactPublicForm($this->getUser()->getContact()); }
    catch ( liEvenementException $e )
    { $this->form = new ContactPublicForm; }
    
    if (!( $this->getUser()->getTransaction()->contact_id && !$request->hasParameter('contact') ))
    {
      // add the contact to the DB
      if ( !$this->form->getObject()->isNew() )
        $this->form->removePassword();
      
      if ( !$request->getParameter('contact', false) && $this->getUser()->getTransaction()->contact_id )
      {
        // it's a hack to avoid infinite loops with the option "app_contact_modify_coordinates_first"
        $data = array();
        foreach ( $this->form->getValidatorSchema()->getFields() as $fieldname => $validator )
        if ( Doctrine::getTable('Contact')->hasColumn($fieldname) )
          $data[$fieldname] = $this->getUser()->getTransaction()->Contact->$fieldname;
      
        $ws = $this->form->getWidgetSchema();
        $vs = $this->form->getValidatorSchema();
        unset($ws['special_groups_list'], $vs['special_groups_list']);
        
        if ( sfConfig::get('app_contact_professional', false) )
        foreach ( array('pro_email' => 'contact_email', 'pro_phone_number' => 'contact_number') as $vname => $field )
          $data[$vname] = $this->form->getObject()->Professionals[0]->$field;
        
        $this->form->bind($data);
      }
      else
         $this->form->bind($request->getParameter('contact'));
      
      try
      {
        if ( !$this->form->isValid() )
        {
          error_log('An error occurred registering a contact ('.$this->form->getErrorSchema().')');
          $this->setTemplate('register');
          return;
        }
      }
      catch ( liOnlineSaleException $e )
      {
        $this->getContext()->getConfiguration()->loadHelpers('I18N');
        $this->getUser()->setFlash('error',__($e->getMessage()));
        return $this->redirect('login/index');
      }
      
      // save the contact, with a non-confirmed attribute
      if ( !$this->getUser()->getTransaction()->contact_id )
        $this->form->getObject()->Transactions[] = $this->getUser()->getTransaction();
      $this->contact = $this->form->save();
    }
    
    // remember the contact's informations
    $this->getUser()->setAttribute('contact_form_values', $this->form->getValues());
    
    // checks if there is no out-of-gauge
    if ( $this->transaction->id == $this->getUser()->getTransactionId() && $this->getUser()->getTransaction()->Tickets->count() > 0 )
    {
      $ids = array();
      foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
        $ids[$ticket->gauge_id] = $ticket->gauge_id;
      
      $q = Doctrine::getTable('Gauge')->createQuery('g')
        ->andWhereIn('g.id',$ids);
      
      if ( !sfConfig::get('app_tickets_count_demands',false) )
      {
        $config = sfConfig::get('app_tickets_vel');
        $q->addSelect("(SELECT count(*) AS nb
                        FROM Ticket tck4
                        WHERE printed_at IS NULL AND integrated_at IS NULL
                          AND transaction_id NOT IN (SELECT o4.transaction_id FROM Order o4)
                          AND duplicating IS NULL AND cancelling IS NULL AND gauge_id = g.id
                          AND id NOT IN (SELECT tck44.cancelling FROM Ticket tck44 WHERE tck44.cancelling IS NOT NULL)
                          AND sf_guard_user_id = '".$this->getUser()->getId()."'
                          AND updated_at > NOW() - '".(isset($config['cart_timeout']) ? $config['cart_timeout'] : 20)." minutes'::interval
                          AND transaction_id != '".$this->getUser()->getTransaction()->id."'
                       ) AS asked_from_vel")
          ->addSelect("(SELECT count(*) AS nb
                        FROM Ticket tck5
                        WHERE printed_at IS NULL AND integrated_at IS NULL
                          AND transaction_id NOT IN (SELECT o5.transaction_id FROM Order o5)
                          AND duplicating IS NULL AND cancelling IS NULL AND gauge_id = g.id
                          AND id NOT IN (SELECT tck55.cancelling FROM Ticket tck55 WHERE tck55.cancelling IS NOT NULL)
                          AND sf_guard_user_id = '".$this->getUser()->getId()."'
                          AND transaction_id = '".$this->getUser()->getTransaction()->id."'
                       ) AS nb_tickets_for_you");
      }
      
      // check for errors / overbooking
      $gauges = $q->execute();
      $this->errors = array();
      foreach ( $gauges as $gauge )
      {
        $free = $gauge->value - $gauge->printed - $gauge->ordered;
        $free -= sfConfig::get('app_tickets_count_demands',false) ? $gauge->asked : $gauge->asked_from_vel;
        $free -= sfConfig::get('app_tickets_count_demands',false) ? 0 : $gauge->nb_tickets_for_you;
        
        if ( $free < 0 )
          $this->errors[] = (string)$gauge->Manifestation;
      }
      if ( count($this->errors) > 0 )
      {
        $this->getContext()->getConfiguration()->loadHelpers('I18N');
        $this->getUser()->setFlash('error',
          format_number_choice(
            '[1]There is one overloaded gauge, please review your command.|(1,+Inf]There are %%nb%% overloaded gauges, please review your command.',
            array('%%nb%%' => count($this->errors)),
            count($this->errors)
          ).' → '.implode(' ; ', $this->errors)
        );
        $this->executeShow($request);
        $this->setTemplate('show');
      }
    }
    
    // surveys to apply
    $surveys = $this->getUser()->getTransaction()->getSurveysToFillIn();
    if ( $surveys->count() > 0 )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('success', __('You are about to complete your order, please fill in those surveys before...'));
      $this->redirect('cart/surveys');
    }
    
    // setting up the vars to commit to the bank
    $redirect = false;
    if ( ($topay = $this->transaction->getPrice(true,true)) > 0 && sfConfig::get('app_payment_type','paybox') != 'onthespot' )
    {
      if (!(
         class_exists($class = ucfirst($plugin = sfConfig::get('app_payment_type','paybox')).'Payment')
      && is_a($class, 'OnlinePaymentInterface', true)
      ))
        throw new liOnlineSaleException('You asked for a payment plugin ('.$plugin.') that does not exist or is not compatible.');
      $this->online_payment = $class::create($this->transaction);
    }
    else // no payment to be done
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      
      $transaction = $this->transaction;
      if ( $transaction->BoughtProducts->count() == 0 && $transaction->Tickets->count() == 0 && $this->MemberCards->count() == 0 )
      {
        $this->getUser()->setFlash('notice', __('Please control your order...'));
        $this->redirect('@homepage');
      }
      
      $transaction->Order[] = new Order;
      $this->createPaymentsDoneByMemberCards();
      $transaction->save();
      
      $this->sendConfirmationEmails($transaction, $this);
      $this->getUser()->resetTransaction();
      if ( $transaction->Payments->count() > 0 )
        $this->getUser()->setFlash('notice',__("Your command has been passed on your member cards, you don't have to pay anything."));
      elseif ( sfConfig::get('app_payment_type', 'paybox') == 'onthespot' )
        $this->getUser()->setFlash('notice',__("Your command has been booked, you will have to pay for it directly with us."));
      
      $redirect = 'transaction/show?id='.$transaction->id;
    }
    
    // empty'ing the password if asked for
    $vel = sfConfig::get('app_tickets_vel', array());
    if ( isset($vel['one_shot']) && $vel['one_shot'] )
    {
      $this->getUser()->getTransaction()->Contact->password = NULL;
      $this->getUser()->getTransaction()->Contact->save();
      error_log('Logout forced following the "one_shot" option.');
      $this->getUser()->logout();
    }
    
    if ( $redirect )
      $this->redirect($redirect);

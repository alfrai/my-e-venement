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
    // add the contact to the DB
    $this->form = new ContactPublicForm($this->getCurrentTransaction()->Contact);
    $this->form->bind($request->getParameter('contact'));
    try {
    if ( !$this->form->isValid() )
    {
      $this->executeRegister($request);
      $this->setTemplate('register');
      return;
    }
    }
    catch ( liOnlineSaleException $e )
    {
      $this->getUser()->setFlash('error',__((string)$e));
      return $this->redirect('login/index');
    }
    
    // remember the contact's informations
    $this->getUser()->setAttribute('contact_form_values', $this->form->getValues());
    
    if ( !($this->getCurrentTransaction() instanceof Transaction) )
      return $this->redirect('event/index');
    
    // checks if there is no out-of-gauge
    $ids = array();
    foreach ( $this->getCurrentTransaction()->Tickets as $ticket )
      $ids[$ticket->gauge_id] = $ticket->gauge_id;
      
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->andWhereIn('g.id',$ids);
    
    if ( !sfConfig::get('app_tickets_count_demands',false) )
    {
      $config = sfConfig::get('app_tickets_vel');
      $q->addSelect("(SELECT count(*) AS nb
                      FROM Ticket tck4
                      WHERE NOT printed AND NOT integrated
                        AND transaction_id NOT IN (SELECT o4.transaction_id FROM Order o4)
                        AND duplicate IS NULL AND cancelling IS NULL AND gauge_id = g.id
                        AND id NOT IN (SELECT tck44.cancelling FROM Ticket tck44 WHERE tck44.cancelling IS NOT NULL)
                        AND sf_guard_user_id = '".$this->getUser()->getId()."'
                        AND updated_at > NOW() - '".(isset($config['cart_timeout']) ? $config['cart_timeout'] : 20)." minutes'::interval
                        AND transaction_id != '".$this->getCurrentTransaction()->id."'
                     ) AS asked_from_vel")
        ->addSelect("(SELECT count(*) AS nb
                      FROM Ticket tck5
                      WHERE NOT printed AND NOT integrated
                        AND transaction_id NOT IN (SELECT o5.transaction_id FROM Order o5)
                        AND duplicate IS NULL AND cancelling IS NULL AND gauge_id = g.id
                        AND id NOT IN (SELECT tck55.cancelling FROM Ticket tck55 WHERE tck55.cancelling IS NOT NULL)
                        AND sf_guard_user_id = '".$this->getUser()->getId()."'
                        AND transaction_id = '".$this->getCurrentTransaction()->id."'
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
        $this->errors[] = $gauge->id;
    }
    if ( count($this->errors) > 0 )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',
        format_number_choice(
          '[1]There is one overloaded gauge, please review your command.|(1,+Inf]There are %%nb%% overloaded gauges, please review your command.',
          array('%%nb%%' => count($this->errors)),
          count($this->errors)
        )
      );
      $this->executeShow($request);
      $this->setTemplate('show');
    }
    
    // save the contact, with a non-confirmed attribute
    if ( !$this->getCurrentTransaction()->contact_id )
      $this->form->getObject()->Transactions[] = $this->getCurrentTransaction();
    $this->contact = $this->form->save();
    
    // setting up the vars to commit to the bank
    $this->online_payment = PayboxPayment::create($this->getCurrentTransaction());

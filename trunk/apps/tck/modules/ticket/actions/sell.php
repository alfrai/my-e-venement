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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    
    if ( $request->getParameter('id') && sfConfig::get('app_transaction_gui', false) == 'touchy' )
    {
      $this->getUser()->setFlash('notice', __('Please use this new GUI'));
      $this->redirect('transaction/edit?id='.$request->getParameter('id'));
    }
    
    if ( !($this->getRoute() instanceof sfObjectRoute) )
    {
      if ( intval($request->getParameter('id')) > 0 )
        $this->redirect('ticket/sell?id='.intval($request->getParameter('id')));
      
      if ( intval($request->getParameter('id')) == 0 )
      {
        if ( $this->getUser()->hasFlash('error') )
          $this->getUser()->setFlash('error',$this->getUser()->getFlash('error'));
        if ( $this->getUser()->hasFlash('notice') )
          $this->getUser()->setFlash('notice',$this->getUser()->getFlash('error'));
        
        $this->transaction = new Transaction();
        $this->transaction->save();
        $this->redirect('ticket/sell?id='.$this->transaction->id.($request->hasParameter('hash') ? '#manifestations-'.$request->getParameter('hash') : ''));
      }
    }
    
    $this->transaction = $this->getRoute()->getObject();
    
    // if closed
    if ( $this->transaction->closed )
    {
      $this->getUser()->setFlash('error',__('You have to re-open the transaction before accessing it'));
      return $this->redirect('ticket/respawn?id='.$this->transaction->id);
    }
    
    // if not a "normal" transaction
    if ( $this->transaction->type != 'normal' )
    {
      $this->getUser()->setFlash('error',__("You can respawn here only normal transactions"));
      if ( $this->transaction->type == 'cancellation' )
        $this->redirect('ticket/pay?id='.$this->transaction->id);
      $this->redirect('ticket/sell');
    }
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->andWhere('NOT p.member_card_linked OR ?',$this->getUser()->hasCredential('tck-member-cards'))
      ->orderBy('p.name');
    $this->prices = $q->execute();
    
    $payment = new Payment();
    $payment->transaction_id = $this->transaction->id;
    $this->payform = new PaymentForm($payment);
    
    $this->createTransactionForm(array('contact_id','professional_id'));

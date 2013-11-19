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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    $this->config = sfConfig::get('app_transaction_touchscreen',array(
      'prices_max_display' => 13,
      'manifs_max_display' => 12,
    ));
    
    if ( !($this->getRoute() instanceof sfObjectRoute) )
    {
      if ( intval($request->getParameter('id')) > 0 )
        $this->redirect('ticket/touchscreen?id='.intval($request->getParameter('id')));
      
      if ( intval($request->getParameter('id')) == 0 )
      {
        if ( $this->getUser()->hasFlash('error') )
          $this->getUser()->setFlash('error',$this->getUser()->getFlash('error'));
        if ( $this->getUser()->hasFlash('notice') )
          $this->getUser()->setFlash('notice',$this->getUser()->getFlash('error'));
        
        $this->transaction = new Transaction();
        $this->transaction->save();
        $this->redirect('ticket/touchscreen?id='.$this->transaction->id);
      }
    }
    
    $this->transaction = $this->getRoute()->getObject();
    
    // if closed
    if ( $this->transaction->closed )
    {
      $this->getUser()->setFlash('error',__('You have to re-open the transaction before to access it'));
      return $this->redirect('ticket/respawn?id='.$this->transaction->id);
    }
    
    // if not a "normal" transaction
    if ( $this->transaction->type != 'normal' )
    {
      $this->getUser()->setFlash('error',__("You can respawn here only normal transactions"));
      if ( $this->transaction->type == 'cancellation' )
        $this->redirect('ticket/pay?id='.$this->transaction->id);
      $this->redirect('ticket/touchscreen');
    }
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->select('p.*')
      ->addSelect('(count(tck.id) / 100) AS nb_tck')
      ->leftJoin('p.Tickets tck ON p.id = tck.price_id AND tck.sf_guard_user_id = ?',$this->getUser()->getId())
      ->andWhere('NOT p.member_card_linked OR ?',$this->getUser()->hasCredential('tck-member-cards'))
      ->orderBy('nb_tck DESC, p.name')
      ->groupBy('p.id, p.name, p.description, p.value, p.online, p.hide, p.member_card_linked, p.created_at, p.updated_at');
    $this->prices = $q->execute();
    
    $payment = new Payment();
    $payment->transaction_id = $this->transaction->id;
    $this->payform = new PaymentForm($payment);
    
    $this->createTransactionForm(array('contact_id','professional_id'));

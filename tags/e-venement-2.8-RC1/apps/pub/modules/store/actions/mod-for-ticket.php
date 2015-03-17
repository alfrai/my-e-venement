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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $this->getContext()->getConfiguration()->loadHelpers('I18N');
  $data = $request->getParameter('link', array());
  foreach ( array('product_id', 'ticket_id') as $field )
  if (!( isset($data[$field]) && intval($data[$field]).'' == ''.$data[$field] ))
  {
    $this->getUser()->setFlash('error', __('An error occurred updating your cart, try again please.'));
    $this->redirect('transaction/show?id='.$this->getUser()->getTransactionId());
  }
  
  $q = Doctrine_Query::create()->from('Product p')
    ->leftJoin('p.Declinations d')
    ->leftJoin('d.BoughtProducts bp WITH bp.transaction_id = ? AND bp.ticket_id = ?', array($this->getUser()->getTransactionId(), $data['ticket_id']))
    ->andWhere('p.id = ?', $data['product_id'])
  ;
  $product = $q->fetchOne();
  foreach ( $product->Declinations as $declination )
  {
    $declination->BoughtProducts->delete();
    if ( $declination->id != $data['declination_id'] )
      continue;
    
    $bp = new BoughtProduct;
    $max = $product->getMostExpansivePrice($this->getUser());
    $bp->Price = $max['price']->Price;
    $bp->ticket_id = $data['ticket_id'];
    $bp->Transaction = $this->getUser()->getTransaction();
    $declination->BoughtProducts[] = $bp;
  }
  $product->save();
  
  $this->getUser()->setFlash('success', __('Your option has been modified in your cart.'));
  $this->redirect('transaction/show?id='.$this->getUser()->getTransactionId());

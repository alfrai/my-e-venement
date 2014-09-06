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
    if ( !isset($this->transaction) )
    $this->transaction = $this->getRoute()->getObject();
    $this->nocancel = $request->hasParameter('nocancel');
    
    $this->totals = array('pet' => 0, 'tip' => 0, 'extra-taxes' => 0, 'vat' => array('total' => 0));
    
    // retrieve tickets
    $q = Doctrine_Query::create()->from('Ticket t')
      ->leftJoin('t.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin("e.Translation et WITH lang = '".$this->getUser()->getCulture()."'")
      ->leftJoin('t.Price p')
      ->andWhere('t.transaction_id = ?',$this->transaction->id)
      ->andWhere('t.duplicating IS NULL')
      ->orderBy('m.happens_at, et.name, p.description, t.value');
    if ( $printed )
      $q->andWhere('t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL OR t.cancelling IS NOT NULL');
    if ( intval($manifestation_id) > 0 )
      $q->andWhere('t.manifestation_id = ?',intval($manifestation_id));
    $this->tickets = $q->execute();
    
    // remove cancelled tickets
    foreach ( $this->tickets as $ticket )
    if ( !$this->nocancel || $ticket->Cancelling->count() == 0 )
    {
      $this->totals['tip'] += $tmp = $ticket->value + $ticket->taxes;
      
      if ( !isset($this->totals['vat'][$ticket->vat]) )
        $this->totals['vat'][$ticket->vat] = array($ticket->manifestation_id => 0);
      if ( !isset($this->totals['vat'][$ticket->vat][$ticket->manifestation_id]) )
        $this->totals['vat'][$ticket->vat][$ticket->manifestation_id] = 0;
      $this->totals['vat'][$ticket->vat][$ticket->manifestation_id] += $tmp = round($tmp - $tmp/(1+$ticket->vat), 2);
      $this->totals['vat']['total'] += $tmp;
    }
    
    foreach ( $this->totals['vat'] as $vat => $manifs )
    if ( is_array($manifs) )
    foreach ( $manifs as $manif )
    {
      if ( is_array($this->totals['vat'][$vat]) )
        $this->totals['vat'][$vat] = 0;
      $this->totals['vat'][$vat] += round($manif,2);
    }
    
    // retrieve products
    $q = Doctrine_Query::create()->from('BoughtProduct bp')
      ->leftJoin('bp.Price p')
      ->andWhere('bp.transaction_id = ?',$this->transaction->id)
      ->orderBy('bp.name, bp.code, bp.declination, bp.price_name, bp.value');
    if ( $printed )
      $q->andWhere('bp.integrated_at IS NOT NULL');
    $this->products = $q->execute();
    
    $this->setLayout('empty');

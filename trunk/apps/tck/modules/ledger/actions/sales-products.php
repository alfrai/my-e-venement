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
    // BE CAREFUL : ALWAYS CHECK Manifestation::getTicketsInfos() FOR CRITERIAS APPLYIANCE FOR BIG LEDGERS
    
    $q = Doctrine::getTable('BoughtProduct')->createQuery('bp')
      ->orderBy('bp.name, bp.code, bp.transaction_id, bp.created_at')
      ->leftJoin('bp.User u')
      ->leftJoin('bp.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
    ;
    $str = 'bp.integrated_at IS NOT NULL';
    if ( !isset($criterias['not-yet-printed']) )
      $q->andWhere($str);
    else
      $q->leftJoin('t.Payments p')
        ->andWhere('t.transaction_id IN (SELECT oo.transaction_id FROM Order oo) OR p.id IS NOT NULL OR '.$str);
    
    if ( !isset($criterias['tck_value_date_payment']) )
      $q->andWhere('(bp.integrated_at >= ? AND bp.integrated_at < ?)',array(
        $dates[0], $dates[1],
      ));
    else
    {
      if ( !$q->contains('LEFT JOIN t.Payments p') )
        $q->leftJoin('t.Payments p');
      $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
          $dates[0], $dates[1],
        ))
        ->andWhere('p.id = (SELECT min(id) FROM Payment p2 WHERE transaction_id = t.id)');
    }
    
    $q->andWhereIn('t.type',array('normal'));
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
    {
      if ( $criterias['users'][''] ) unset($criterias['users']['']);
      if ( !isset($criterias['tck_value_date_payment']) )
        $q->andWhereIn('bp.sf_guard_user_id',$criterias['users']);
      else
      {
        if ( !$q->contains('LEFT JOIN t.Payments p') )
          $q->leftJoin('t.Payments p');
        $q->andWhereIn('p.sf_guard_user_id',$criterias['users']);
      }
    }
    
    // contact/organism
    foreach ( array('contact_id' => 'c.id', 'organism_id' => 'o.id') as $criteria => $field )
    if ( isset($criterias[$criteria]) )
      $q->andWhere($field.' = ?', $criterias[$criteria]);
    
    // check if there are too many tickets to display them correctly
    $this->nb_products = $q->count();
    
    // restrict the query if so...
    if ( $this->nb_products > sfConfig::get('app_ledger_max_tickets',5000) )
      $q->select('e.*, m.*, l.*');
    
    $this->products = $q->execute();
    $this->dates = $dates;
    
    // total initialization / including taxes
    $this->products_total = array('qty' => 0, 'vat' => array(), 'value' => 0, 'taxes' => 0);
    foreach ( array('vat', 'shipping_fees_vat') as $field )
    {
      $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
      $q = 'SELECT DISTINCT '.$field.' AS vat FROM bought_product WHERE '.$field.' != 0';
      $stmt = $pdo->prepare($q);
      $stmt->execute();
      foreach ( $arr = $stmt->fetchAll() as $vat )
      {
        if ( isset($this->total) && isset($this->total['vat']) )
          $this->total['vat'][$vat['vat']] = 0;
        $this->products_total['vat'][$vat['vat']] = 0;
      }
    }
    ksort($this->total['vat']);

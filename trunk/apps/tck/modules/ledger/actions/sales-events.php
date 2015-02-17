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
    
    $q = Doctrine::getTable('Event')->createQuery('e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.User u')
      ->andWhere('tck.duplicating IS NULL') // to count only originals tickets, not duplicates
      ->leftJoin('tck.Transaction t')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->orderBy('translation.name, m.happens_at, l.name, tck.price_name, u.first_name, u.last_name, tck.sf_guard_user_id, tck.cancelling IS NULL DESC, tck.updated_at');
    
    $str = 'tck.printed_at IS NOT NULL OR tck.cancelling IS NOT NULL OR tck.integrated_at IS NOT NULL';
    if ( !isset($criterias['not-yet-printed']) )
      $q->andWhere($str);
    else
      $q->leftJoin('t.Payments p')
        ->andWhere('t.transaction_id IN (SELECT oo.transaction_id FROM Order oo) OR p.id IS NOT NULL OR '.$str);
    
    if ( !isset($criterias['tck_value_date_payment']) )
      $q->andWhere('(tck.cancelling IS NOT NULL AND tck.created_at >= ? AND tck.created_at < ? OR tck.cancelling IS NULL AND (tck.printed_at IS NOT NULL AND tck.printed_at >= ? AND tck.printed_at < ? OR tck.integrated_at IS NOT NULL AND tck.integrated_at >= ? AND tck.integrated_at < ?))',array(
          $dates[0], $dates[1],
          $dates[0], $dates[1],
          $dates[0], $dates[1],
        ));
    else
    {
      if ( !$q->contains('LEFT JOIN t.Payments p') )
        $q->leftJoin('t.Payments p');
      $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
          $dates[0],
          $dates[1],
        ))
        ->andWhere('p.id = (SELECT min(id) FROM Payment p2 WHERE transaction_id = t.id)');
    }
    
    $q->andWhereIn('t.type',array('normal', 'cancellation'));
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
    {
      if ( $criterias['users'][''] ) unset($criterias['users']['']);
      if ( !isset($criterias['tck_value_date_payment']) )
        $q->andWhereIn('tck.sf_guard_user_id',$criterias['users']);
      else
      {
        if ( !$q->contains('LEFT JOIN t.Payments p') )
          $q->leftJoin('t.Payments p');
        $q->andWhereIn('p.sf_guard_user_id',$criterias['users']);
      }
    }
    
    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && $criterias['workspaces'][0] )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && $criterias['manifestations'][0] )
      $q->andWhereIn('g.manifestation_id',$criterias['manifestations']);
    if ( isset($criterias['contact_id']) && $criterias['contact_id'] )
      $q->andWhere('tck.contact_id = ? OR t.contact_id = ?', array($criterias['contact_id'], $criterias['contact_id']));
    if ( isset($criterias['organism_id']) && $criterias['organism_id'] )
      $q->andWhere('o.id = ?', $criterias['organism_id']);

    // check if there are too many tickets to display them correctly
    $test = $q->copy();
    $events = $test->select('e.id, count(DISTINCT tck.id) AS nb_tickets')
      ->groupBy('e.id')
      ->orderBy('e.id')
      ->fetchArray();
    $this->nb_tickets = 0;
    foreach ( $events as $event )
      $this->nb_tickets += $event['nb_tickets'];
    
    // restrict the query if so...
    if ( $this->nb_tickets > sfConfig::get('app_ledger_max_tickets',5000) )
      $q->select('e.*, m.*, l.*');
    
    $this->events = $q->execute();
    $this->dates = $dates;
    
    // total initialization / including taxes
    $this->total = array('qty' => 0, 'vat' => array(), 'value' => 0, 'taxes' => 0);
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = 'SELECT DISTINCT vat FROM ticket WHERE vat != 0';
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    foreach ( $arr = $stmt->fetchAll() as $vat )
    {
      if ( isset($this->products_total) && isset($this->products_total['vat']) )
        $this->products_total['vat'][$vat['vat']] = 0;
      $this->total['vat'][$vat['vat']] = 0;
    }
    ksort($this->total['vat']);

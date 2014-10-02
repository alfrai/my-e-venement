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
  $this->debug($request);
  $this->data = array();
  $params = $request->getParameter('price_new');
  $config = sfConfig::get('app_tickets_vel', array());
  
  if (!( isset($params['manifestation_id']) && intval($params['manifestation_id']).'' === ''.$params['manifestation_id'] && intval($params['manifestation_id']) > 0 ))
    return 'Error';
  if (!( isset($params['price_id']) && intval($params['price_id']).'' === ''.$params['price_id'] && intval($params['price_id']) > 0 ))
    return 'Error';
  
  // retrieve the gauge where can be applyied the future ticket
  $q = Doctrine::getTable('Gauge')->createQuery('g', false)
    ->andWhere('g.manifestation_id = ?', $params['manifestation_id'])
    ->andWhere('g.group_name = ?', $params['group_name'])
    ->andWhere('g.online = ?', true)
    
    ->leftJoin('g.PriceGauges         gpg WITH gpg.price_id IN (SELECT gup.price_id FROM UserPrice gup WHERE gup.sf_guard_user_id = ?)', $this->getUser()->getId())
    ->leftJoin('g.Manifestation m')
    ->leftJoin('m.PriceManifestations mpm WITH mpm.price_id IN (SELECT mup.price_id FROM UserPrice mup WHERE mup.sf_guard_user_id = ?)', $this->getUser()->getId())
    ->andWhere('(gpg.price_id = ? OR mpm.price_id = ?)', array($params['price_id'], $params['price_id']))
    
    ->leftJoin('g.Workspace ws')
    ->leftJoin('ws.SeatedPlans sp WITH sp.location_id = m.location_id')
    ->leftJoin('sp.Seats s')
    ->leftJoin('s.Tickets tck WITH tck.gauge_id = g.id')
    ->andWhere('tck.id IS NULL')
    
    ->orderBy('min(s.rank), gpg.value DESC, ws.name')
    ->select($select = 'g.id, m.id, m.online_limit, gpg.id, gpg.value, ws.id, ws.name')
    ->addSelect('count(DISTINCT s.id) AS nb_seats')
    ->groupBy($select)
  ;
  $gauges = $q->execute();
  if ( $gauges->count() == 0 )
  {
    error_log('No gauge found for this ticket ('.print_r($params,true).')');
    return 'Error';
  }
  
  $success = false;
  foreach ( $gauges as $gauge )
  {
    // limitting the max quantity, especially for prices linked to member cards
    $vel = sfConfig::get('app_tickets_vel');
    $vel['max_per_manifestation'] = $this->getMaxPerManifestation($gauge->Manifestation);
    
    // gauge limits
    $tmp = Doctrine::getTable('Gauge')->createQuery('g')->andWhere('g.id = ?', $gauge->id)->fetchOne();
    $free = $tmp->value - $tmp->printed - $tmp->ordered - (sfConfig::get('project_tickets_count_demands', false) ? $tmp->asked : 0);
    $max = $vel['max_per_manifestation'] < $free ? $vel['max_per_manifestation'] : $free;
    if ( $gauge->nb_seats > $max )
    {
      $success = true;
      break;
    }
  }
  
  if ( !$success )
  {
    error_log('The maximum number of tickets is reached for online sales on manifestation #'.$gauge->manifestation_id.' and gauges '.$params['group_name']);
    return 'Error';
  }

  if ( Doctrine::getTable('Ticket')->createQuery('tck')
    ->andWhere('tck.manifestation_id = ?', $gauge->manifestation_id)
    ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
    ->count() >= $vel['max_per_manifestation'] )
  {
    $this->message = 'Some tickets have not been added because you reached the limit of tickets for this manifestation.';
    return 'Success';
  }
  
  $ticket = new Ticket;
  $ticket->transaction_id = $this->getUser()->getTransactionId();
  $ticket->price_id = $params['price_id'];
  $ticket->gauge_id = $gauge->id;
  $ticket->save();
  
  return 'Success';

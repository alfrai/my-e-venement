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
    $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
    if ( $gauge->Manifestation->online_limit_per_transaction && $gauge->Manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
      $vel['max_per_manifestation'] = $gauge->Manifestation->online_limit_per_transaction;
    
    // max per manifestation per contact ...
    $vel['max_per_manifestation_per_contact'] = isset($vel['max_per_manifestation_per_contact']) ? $vel['max_per_manifestation_per_contact'] : false;
    if ( $vel['max_per_manifestation_per_contact'] > 0 )
    {
      $max = $vel['max_per_manifestation_per_contact'];
      foreach ( $sf_user->getContact()->Transactions as $transaction )
      if ( $transaction->id != $sf_user->getTransaction()->id )
      foreach ( $transaction->Tickets as $ticket )
      if (( $ticket->transaction_id == $sf_user->getTransaction()->id || $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
        && !$ticket->hasBeenCancelled()
        && $gauge->Manifestation->id == $ticket->manifestation_id
      )
      {
        $vel['max_per_manifestation_per_contact']--;
      }
      $vel['max_per_manifestation'] = $vel['max_per_manifestation'] > $vel['max_per_manifestation_per_contact']
        ? $vel['max_per_manifestation_per_contact']
        : $vel['max_per_manifestation'];
    }
    
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
  
  $ticket = new Ticket;
  $ticket->transaction_id = $this->getUser()->getTransactionId();
  $ticket->price_id = $params['price_id'];
  $ticket->gauge_id = $gauge->id;
  $ticket->save();
  
  return 'Success';
?>


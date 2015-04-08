<?php
  foreach ( $event->Manifestations as $manif )
  if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
  {
    $tmp = 0;
    $qty += $manif->Tickets->count();
    foreach ( $manif->Tickets as $ticket )
    {
      if ( !is_null($ticket->cancelling) )
        $qty -= 2;
      
      // extremely weird behaviour, only for specific cases... it's about an early error in the VAT calculation in e-venement
      $time = strtotime($ticket->cancelling ? $ticket->created_at : ($ticket->printed_at ? $ticket->printed_at : $ticket->integrated_at));
      $tmp = sfConfig::get('app_ledger_sum_rounding_before',false) && $time < strtotime(sfConfig::get('app_ledger_sum_rounding_before'))
        ? $ticket->value + $ticket->taxes - ($ticket->value+$ticket->taxes) / (1+$ticket->vat) // exception
        : round($ticket->value + $ticket->taxes - ($ticket->value+$ticket->taxes) / (1+$ticket->vat),2); // regular
      
      // taxes feeding
      $vat[$ticket->vat][$event->id][$manif->id]
        += $tmp;
      
      // total feeding
      $total['vat'][$ticket->vat] += $tmp;
      $total['value'] += $ticket->value;
      $value += $ticket->value;
      $total['taxes'] += $ticket->taxes;
      $taxes += $ticket->taxes;
    }
  }
  else // more tickets than the limit
  {
    $infos[$manif->id] = $manif->getInfosTickets($sf_data->getRaw('options'));
    
    $total['value'] += $infos[$manif->id]['value'];
    $value += $infos[$manif->id]['value'];
    $taxes += $infos[$manif->id]['taxes'];
    $qty += $infos[$manif->id]['qty'];
    
    foreach ( $infos[$manif->id]['vat'] as $rate => $amount )
    {
      $vat[$rate][$event->id][$manif->id] = $amount; // taxes feeding
      $total['vat'][$rate] += $amount; // total feeding
    }
  } // endif; endforeach;
  
  // extremely weird behaviour, only for specific cases... it's about an early mysanalysis in the VAT calculation in e-venement
  if ( sfConfig::get('app_ledger_sum_rounding_before',false) && strtotime(sfConfig::get('app_ledger_sum_rounding_before',false)) > strtotime($dates[0]) )
  {
    // initialization
    foreach ( $total['vat'] as $rate => $amount )
      $total['vat'][$rate] = 0;
    
    // completions
    foreach ( $vat as $rate => $content )
    foreach ( $content as $event_id => $manifs )
    if ( $event_id !== 'total' )
    foreach ( $manifs as $manif_id => $manif )
    {
      $vat[$rate][$event_id][$manif_id] = round($manif,2);
      $total['vat'][$rate] += round($manif,2);
    }
  }
?>

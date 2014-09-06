<?php
    // total qty
    foreach ( $events as $event )
    foreach ( $event->Manifestations as $manif )
    {
      // taxes initialization
      foreach ( $total['vat'] as $key => $value )
      {
        if ( !isset($vat[$key]) )
          $vat[$key] = array('total' => 0);
        if ( !isset($vat[$key][$event->id]) )
          $vat[$key][$event->id] = array();
        $vat[$key][$event->id][$manif->id] = 0;
      }

      if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
      foreach ( $manif->Tickets as $ticket )
        $total['qty'] += is_null($ticket->cancelling)*2-1;
    }
?>

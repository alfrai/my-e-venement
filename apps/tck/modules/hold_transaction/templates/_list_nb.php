<?php
      $cpt = 0;
      foreach ( $hold_transaction->Transaction->Tickets as $ticket )
      if ( !$ticket->cancelling && !$ticket->hasBeenCancelled() && !$ticket->duplicating )
      if ( $ticket->seat_id )
        $cpt++;
      echo $hold_transaction->pretickets > $cpt ? $hold_transaction->pretickets : $cpt;
?>

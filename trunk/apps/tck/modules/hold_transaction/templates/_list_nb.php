<?php
      $cpt = 0;
      foreach ( $hold_transaction->Transaction->Tickets as $ticket )
      if ( !$ticket->cancelling && !$ticket->hasBeenCancelled() && !$ticket->duplicating )
        $cpt++;
      echo $hold_transaction->pretickets + $cpt;
?>

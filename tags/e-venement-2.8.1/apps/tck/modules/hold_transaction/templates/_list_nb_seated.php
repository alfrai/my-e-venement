<?php
      $cpt = 0;
      foreach ( $hold_transaction->Transaction->Tickets as $ticket )
      if ( !$ticket->cancelling && !$ticket->hasBeenCancelled() && !$ticket->duplicating )
      if ( $ticket->seat_id )
        $cpt++;
?>
<span class="<?php echo $cpt == 0 ? 'li-warning' : '' ?>"><?php echo $cpt ?></span>

<?php use_helper('Number') ?>
<?php
  echo format_currency(100*$hold->nb_seated_tickets/$hold->nb_seats,'%').' ('.$hold->nb_seated_tickets.'/'.$hold->nb_seats.')';

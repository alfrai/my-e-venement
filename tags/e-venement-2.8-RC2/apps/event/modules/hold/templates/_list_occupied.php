<?php use_helper('Number') ?>
<?php if ( $hold->nb_seats == 0 ): ?>
<?php echo '- ('.$hold->nb_seated_tickets.'/'.$hold->nb_seats.')' ?>
<?php else: ?>
<?php echo format_currency(100*$hold->nb_seated_tickets/$hold->nb_seats,'%').' ('.$hold->nb_seated_tickets.'/'.$hold->nb_seats.')' ?>
<?php endif ?>

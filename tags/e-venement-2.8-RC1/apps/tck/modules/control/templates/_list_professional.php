<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<ul>
<?php if ( $control->Ticket->Transaction->professional_id ): ?>
  <li class="professional"><?php echo $control->Ticket->Transaction->Professional->name_type ?></li>
  <li class="organism"><?php echo cross_app_link_to($control->Ticket->Transaction->Professional->Organism, 'rp', 'organism/show?id='.$control->Ticket->Transaction->Professional->organism_id) ?></li>
<?php endif ?>
</ul>
<?php endif ?>

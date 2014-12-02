<?php use_helper('CrossAppLink') ?>
<td><?php echo __('Contact') ?></td>
<td>
  <?php echo cross_app_link_to($ticket->Transaction->Contact, 'rp', 'contact/show?id='.$ticket->Transaction->contact_id) ?>
  <span class="picto"><?php echo $sf_data->getRaw('ticket')->Transaction->Contact->groups_picto ?></span>
</td>
<td>
  <?php if ( $ticket->Transaction->professional_id ): ?>
  <?php echo $ticket->Transaction->Professional->name ? $ticket->Transaction->Professional->name : $ticket->Transaction->Professional->ProfessionalType ?>
  <span class="picto"><?php echo $sf_data->getRaw('ticket')->Transaction->Professional->groups_picto ?></span>
  <br/>
  <?php echo $ticket->Transaction->Professional->Organism ?>
  <?php endif ?>
</td>
<td></td>


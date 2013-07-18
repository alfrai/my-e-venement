<td><?php echo __('Contact') ?></td>
<td>
  <?php echo $ticket->Transaction->Contact ?>
  <span class="picto"><?php echo $ticket->Transaction->Contact->getRaw('groups_picto') ?></span>
</td>
<td>
  <?php if ( $ticket->Transaction->professional_id ): ?>
  <?php echo $ticket->Transaction->Professional->name ? $ticket->Transaction->Professional->name : $ticket->Transaction->Professional->ProfessionalType ?>
  <span class="picto"><?php echo $ticket->Transaction->Professional->getRaw('groups_picto') ?></span>
  <br/>
  <?php echo $ticket->Transaction->Professional->Organism ?>
  <?php endif ?>
</td>
<td></td>


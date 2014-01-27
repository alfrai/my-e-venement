<td><?php echo __('Ticket control',NULL,'menu') ?></td>
<?php if ( $ticket->Controls->count() == 0 ): ?>
<td><?php echo __('No control') ?></td>
<td></td>
<?php else: ?>
<?php
  $dates = $users = array();
  foreach ( $ticket->Controls as $control )
  {
    $dates[] = format_datetime($control->updated_at);
    $users[] = (string)$control->User;
  }
?>
<td><ol><li>
  <?php echo implode("</li>\n<li>",$dates); ?>
</li></ol></td>
<td><ol><li>
  <?php echo implode("</li>\n<li>",$users); ?>
</li></ol></td>
<?php endif ?>
<td></td>


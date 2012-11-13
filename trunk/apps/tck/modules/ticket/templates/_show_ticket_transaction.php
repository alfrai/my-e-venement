<?php use_helper('CrossAppLink') ?>

<td><?php echo __('Transaction') ?></td>
<td>#<?php echo link_to($ticket->Transaction,'ticket/sell?id='.$ticket->Transaction->id) ?></td>
<td class="state">
  <table>
    <tbody><tr>
      <td><?php echo image_tag( $ticket->printed ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
    </tr></tbody>
    <thead><tr>
      <td><?php echo __('Printed') ?></td>
    </tr></thead>
  </table>
  <table>
    <tbody><tr>
      <td><?php echo image_tag( $ticket->integrated ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
    </tr></tbody>
    <thead><tr>
      <td><?php echo __('Integrated') ?></td>
    </tr></thead>
  </table>
</td>
<td></td>

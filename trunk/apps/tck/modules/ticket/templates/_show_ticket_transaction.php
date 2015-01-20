<?php use_helper('CrossAppLink') ?>

<td><?php echo __('Transaction') ?></td>
<td>#<?php echo link_to($ticket->Transaction->id,'ticket/'.($ticket->Transaction->type == 'normal' ? 'sell' : 'pay').'?id='.$ticket->Transaction->id) ?></td>
<td class="state">
  <table title="<?php echo format_datetime($ticket->printed_at) ?>">
    <tbody><tr>
      <td><?php echo image_tag( $ticket->printed_at ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
    </tr></tbody>
    <thead><tr>
      <td><?php echo __('Printed') ?></td>
    </tr></thead>
  </table>
  <table title="<?php echo $ticket->cancelling ? format_datetime($ticket->created_at) : '' ?>">
    <tbody><tr>
      <td><?php echo image_tag( $ticket->cancelling ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
    </tr></tbody>
    <thead><tr>
      <td><?php echo __('Cancellation') ?></td>
    </tr></thead>
  </table>
  <table title="<?php echo format_datetime($ticket->integrated_at) ?>">
    <tbody><tr>
      <td><?php echo image_tag( $ticket->integrated_at ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
    </tr></tbody>
    <thead><tr>
      <td><?php echo __('Integrated') ?></td>
    </tr></thead>
  </table>
  <?php if ( sfConfig::get('app_tickets_id', 'id') == 'barcode' && ($ticket->printed_at || $ticket->integrated_at) ): ?>
    <img class="qrcode" src="data:image/jpg;base64,<?php echo base64_encode($ticket->getRawValue()->getBarcodePng()) ?>" alt="#<?php echo $ticket->id ?>" />
  <?php endif ?>
  </td>
<td></td>

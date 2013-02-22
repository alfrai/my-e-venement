<?php use_helper('Number') ?>
<?php if ( $member_card->Tickets->count() > 0 ): ?>
<div class="sf_admin_form_row">
  <label><?php echo __('List of tickets') ?>:</label>
  <table class="tickets_list ui-widget ui-corner-all ui-widget-content">
  <tbody>
  <?php foreach ( $member_card->Tickets as $ticket ): ?>
  <?php if ( $ticket->Duplicatas->count() == 0 ): ?>
    <tr>
      <td><?php echo cross_app_link_to('#'.$ticket->transaction_id,'tck','ticket/sell?id='.$ticket->transaction_id) ?></td>
      <td><?php echo $ticket->price_name ?></td>
      <td><?php echo format_currency($ticket->value,'€') ?></td>
      <td><?php echo cross_app_link_to($ticket->Manifestation,'event','manifestation/show?id='.$ticket->manifestation_id) ?></td>
    </tr>
  <?php endif ?>
  <?php endforeach ?>
  </tbody>
  </table>
</div>
<?php endif ?>

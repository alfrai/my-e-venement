<?php include_partial('global/flashes') ?>
<?php use_helper('Date') ?>

<p>#<?php echo $transaction_id ?></p>

<?php $last = array('event_id' => 0, 'manifestation_id' => 0, 'gauge_id' => 0) ?>
<?php $nb_ws = 0 ?>
<?php $total = array('qty' => 0, 'value' => 0) ?>

<table id="command">
<tbody>
<?php foreach ( $events as $event ): ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
<?php foreach ( $manif->Gauges as $gauge ): ?>
<?php foreach ( $gauge->Tickets as $ticket ): ?>
<tr id="gauge-<?php echo $gauge->id ?>" class="<?php if ( in_array($gauge->id,$sf_data->getRaw('errors')) ) echo 'overbooked' ?>">
  <td class="event"><?php if ( $last['event_id'] != $event->id ) { $last['event_id'] = $event->id; echo $event; } ?></td>
  <td class="manifestation"><?php if ( $last['manifestation_id'] != $manif->id ) { $last['manifestation_id'] = $manif->id; echo $manif->getFormattedDate(); } ?></td>
  <td class="workspace"><?php if ( $manif->Gauges->count() > 1 && $last['gauge_id'] != $gauge->id ): ?>
    <?php echo $gauge->Workspace ?>
    <?php $nb_ws++ ?>
  <?php endif ?></td>
  <?php $total['qty']++; $total['value'] += $ticket->value; include_partial('show_ticket',array('ticket' => $ticket)) ?>
  <td class="mod"><?php echo link_to(__('modify'),'manifestation/show?id='.$manif->id) ?></td>
  <?php $last['gauge_id'] = $gauge->id; ?>
</tr>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>
<?php foreach ( $member_cards as $mc ): ?>
<tr id="mct-<?php echo $mc->member_card_type_id ?>" class="">
  <td class="event"><?php echo $mc->MemberCardType->description ? $mc->MemberCardType->description : $mc->MemberCardType ?></td>
  <td class="manifestation"><span class="mct-<?php echo $mc->member_card_type_id ?>"><?php echo format_date($mc->expire_at,'P') ?></td>
  <td class="workspace"></td>
  <td class="tickets"><span class="mct-<?php echo $mc->member_card_type_id ?>"><?php echo $mc->MemberCardType ?></td>
  <?php $total['qty']++; $total['value'] += $mc->MemberCardType->value ?>
  <td class="qty">1</td>
  <td class="value"><?php use_helper('Number'); echo format_currency($mc->MemberCardType->value,'€') ?></td>
  <td class="total"><?php echo format_currency($mc->MemberCardType->value,'€') ?></td>
  <td class="mod"><?php echo link_to(__('modify'),'card/index') ?></td>
</tr>
<?php endforeach ?>
</tbody>
<tfoot>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td class="qty"><?php echo $total['qty'] ?></td>
    <td></td>
    <td class="total"><?php use_helper('Number'); echo format_currency($total['value'],'€'); ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td><?php echo __('Event') ?></td>
    <td><?php echo __('Date') ?></td>
    <td><?php if ( $nb_ws > 0 ) echo __('Space') ?></td>
    <td><?php echo __('Price') ?></td>
    <td><?php echo __('Qty') ?></td>
    <td><?php echo __('Unit price') ?></td>
    <td><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

<?php include_partial('show_js') ?>
<?php include_partial('show_order') ?>

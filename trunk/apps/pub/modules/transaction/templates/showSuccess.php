<?php use_javascript('pub-cart?'.date('Ymd')) ?>
<?php include_partial('global/flashes') ?>
<?php use_helper('Number'); ?>
<?php use_helper('Date') ?>

<div id="title">
  <h1><?php echo __('Command summary') ?></h1>
  <p><b><?php echo __('Transaction number') ?>:</b> #<?php echo $transaction->id ?> <b><?php echo __('Edition date') ?>:</b> <?php echo format_datetime(date('Y-m-d H:i:s'),'f') ?></p>
  <p><b><?php echo __('Contact') ?>:</b> <?php echo $sf_user->hasContact() ? $sf_user->getContact() : 'N/A' ?></p>
</div>

<?php include_partial('global/ariane', array('active' => $current_transaction ? 2 : 0)) ?>

<?php $last = array('event_id' => 0, 'manifestation_id' => 0, 'gauge_id' => 0) ?>
<?php $nb_ws = 0 ?>
<?php $total = array('qty' => 0, 'value' => 0, 'taxes' => 0, 'mc_qty' => 0, 'mc_value' => 0) ?>

<?php $for_links = array() ?>

<table id="command">
<tbody>
<?php foreach ( $events as $event ): ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
<?php foreach ( $manif->Gauges as $gauge ): ?>
<?php foreach ( $gauge->Tickets as $ticket ): ?>
<?php $for_links[] = $ticket->Manifestation ?>
<tr
  data-manifestation-id="<?php echo $manif->id ?>"
  data-gauge-id="<?php echo $gauge->id ?>"
  data-event-id="<?php echo $event->id ?>"
  id="ticket-<?php echo $ticket->id ?>"
  class="tickets <?php if ( in_array($gauge->id,$sf_data->getRaw('errors')) ) echo 'overbooked' ?>"
>
  <?php if ( sfConfig::get('app_options_synthetic_plans', false) ): ?>
  <td class="picture"><?php echo $event->Picture->getRawValue()->render(array('app' => 'pub')) ?></td>
  <td class="event">
    <p><?php echo $event ?></p>
    <p><?php echo $manif->getFormattedDate() ?></p>
  </td>
  <td class="manifestation">
    <p class="seat"><?php echo __('Seat #').$ticket->Seat ?></p>
    <?php if ( $ticket->contact_id ): ?>
    <p class="contact"><?php echo $ticket->DirectContact->coolname ?></p>
    <p class="email"><a href="mailto:<?php echo $ticket->DirectContact->email ?>"><?php echo $ticket->DirectContact->email ?></a></p>
    <?php endif ?>
  </td>
  <?php else: ?>
  <td class="picture"><?php echo $event->Picture->getRawValue()->render() ?></td>
  <td class="event"><?php if ( $last['event_id'] != $event->id ) { $last['event_id'] = $event->id; echo $event; } ?></td>
  <td class="manifestation"><?php if ( $last['manifestation_id'] != $manif->id ) { $last['manifestation_id'] = $manif->id; echo $manif->getFormattedDate(); } ?></td>
  <?php endif ?>
  <td class="workspace"><?php if ( $manif->Gauges->count() > 1 && $last['gauge_id'] != $gauge->id || sfConfig::get('app_options_synthetic_plans', false) ): ?>
    <?php echo $gauge->Workspace ?>
    <?php $nb_ws++ ?>
  <?php endif ?></td>
  <?php
    if ( $ticket->price_id )
    {
      $total[$ticket->Price->member_card_linked ? 'mc_qty' : 'qty']++;
      $total[$ticket->Price->member_card_linked ? 'mc_value' : 'value'] += $ticket->value;
      $total['taxes'] += $ticket->taxes;
    }
  ?>
  <?php include_partial('show_ticket',array('ticket' => $ticket)) ?>
  <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
  <td class="linked-stuff"><?php include_partial('show_linked_stuff', array('ticket' => $ticket))  ?></td>
  <?php endif ?>
  <?php $last['gauge_id'] = $gauge->id; ?>
  <td class="mod"><?php echo link_to(__('modify'),'manifestation/show?id='.$manif->id) ?></td>
</tr>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>
<?php foreach ( $member_cards as $mc ): ?>
<tr id="mct-<?php echo $mc->member_card_type_id ?>" class="member_cards" data-mct-id="<?php echo $mc->member_card_type_id ?>">
  <td class="picture"></td>
  <td class="event"><?php echo $mc->MemberCardType->description ? $mc->MemberCardType->description : $mc->MemberCardType ?></td>
  <td class="manifestation"><span class="mct-<?php echo $mc->member_card_type_id ?>"><?php echo format_date($mc->expire_at,'P') ?></span></td>
  <td class="workspace"></td>
  <td class="tickets"><span data-mct-id="<?php echo $mc->member_card_type_id ?>" class="mct-<?php echo $mc->member_card_type_id ?>"><?php echo $mc->MemberCardType ?></span></td>
  <?php $total['qty']++; $total['value'] += $mc->MemberCardType->value ?>
  <?php if ( !sfConfig::get('app_options_synthetic_plans', false) ): ?>
  <td class="qty">1</td>
  <td class="value"><?php echo format_currency($mc->MemberCardType->value,'€') ?></td>
  <?php endif ?>
  <td class="total"><?php echo format_currency($mc->MemberCardType->value,'€') ?></td>
  <td class="extra-taxes" title="<?php echo __('Booking fees') ?>"></td>
  <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
  <td class="linked-stuff"></td>
  <?php endif ?>
  <td class="mod"><?php if ( $current_transaction ) echo link_to(__('modify'),'card/index') ?></td>
</tr>
<?php endforeach ?>
<?php foreach ( $products as $product ): ?>
<?php $for_links[] = $product->Declination->Product ?>
<tr class="products">
  <td class="picture"></td>
  <td class="event"><?php echo $product->Declination->Product->Category ?></td>
  <td class="manifestation"><?php echo $product->Declination->Product->short_name ? $product->Declination->Product->short_name : $product ?></td>
  <td class="workspace"><?php echo $product->declination ?></td>
  <td class="tickets">
    <span class="price-<?php echo $product->price_id ?>" data-price-id="<?php echo $product->price_id ?>">
      <?php if ( $product->price_id ): ?>
        <?php echo $product->Price->description ? $product->Price->description : $product->Price ?>
      <?php else: ?>
        <?php echo $product->price_name ?>
      <?php endif ?>
    </span>
  </td>
  <?php $total['qty']++; $total['value'] += $product->value ?>
  <?php if ( !sfConfig::get('app_options_synthetic_plans', false) ): ?>
  <td class="qty">1</td>
  <td class="value"><?php echo format_currency($product->value,'€') ?></td>
  <?php endif ?>
  <td class="total"><?php echo format_currency($product->value,'€') ?></td>
  <td class="extra-taxes" title="<?php echo __('Booking fees') ?>"></td>
  <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
  <td class="linked-stuff"></td>
  <?php endif ?>
  <td class="mod">
    <?php if ( $product->product_declination_id && $product->Declination->Product->Category->online && $current_transaction && !$product->ticket_id ): ?>
      <?php echo link_to(__('modify'),'store/edit?id='.$product->Declination->Product->id) ?>
    <?php endif ?>
  </td>
</tr>
<?php endforeach ?>
</tbody>
<tfoot>
  <?php if ( $total['mc_qty'] ): ?>
  <tr class="total">
    <td class="picture"></td>
    <td class="type"><?php echo __('Total') ?></td>
    <td></td>
    <td></td>
    <td class="qty"><?php echo $total['mc_qty'] + $total['qty'] ?></td>
    <?php if ( !sfConfig::get('app_options_synthetic_plans', false) ): ?>
    <td></td>
    <td></td>
    <?php endif ?>
    <td class="total"><?php echo format_currency($total['value']+$total['mc_value'],'€'); ?></td>
    <td></td>
    <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
    <td class="linked-stuff"></td>
    <?php endif ?>
    <td></td>
  </tr>
  <tr class="mc">
    <td class="picture"></td>
    <td class="type"><?php echo __("Passed on member card") ?></td>
    <td></td>
    <td></td>
    <td class="qty">(<?php echo $total['mc_qty'] ?>)</td>
    <?php if ( !sfConfig::get('app_options_synthetic_plans', false) ): ?>
    <td></td>
    <td></td>
    <?php endif ?>
    <td class="total"><?php echo format_currency(-$total['mc_value'],'€'); ?></td>
    <td></td>
    <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
    <td class="linked-stuff"></td>
    <?php endif ?>
    <td></td>
  </tr>
  <?php endif ?>
  <tr class="topay">
    <td class="picture"></td>
    <td class="type"><?php echo $total['mc_qty'] ? __('To pay') : __('Total') ?></td>
    <td></td>
    <td></td>
    <?php if ( !sfConfig::get('app_options_synthetic_plans', false) ): ?>
    <td></td>
    <td></td>
    <?php endif ?>
    <td class="qty"><?php echo $total['qty'] ?></td>
    <td class="total"><?php echo format_currency($total['value'],'€'); ?></td>
    <td class="extra-taxes"><?php echo format_currency($total['taxes'],'€'); ?></td>
    <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
    <td class="linked-stuff"></td>
    <?php endif ?>
    <td class="total-total"><?php echo format_currency($total['value']+$total['taxes'],'€'); ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="picture"></td>
    <td class="product"><?php echo __('Product') ?></td>
    <td class="declination"><?php echo __('Declination') ?></td>
    <td class="space"><?php if ( $nb_ws > 0 ) echo __('Space') ?></td>
    <td><?php echo __('Price') ?></td>
    <?php if ( !sfConfig::get('app_options_synthetic_plans', false) ): ?>
    <td class="qty"><?php echo __('Qty') ?></td>
    <td><?php echo __('Unit price') ?></td>
    <?php endif ?>
    <td><?php echo sfConfig::get('app_options_synthetic_plans', false) ? '' : __('Total') ?></td>
    <td title="<?php echo __('Booking fees') ?>"><?php echo __('Fees') ?></td>
    <?php if ( sfConfig::get('app_options_synthetic_plans', false) && $current_transaction ): ?>
    <td class="linked-stuff"><?php echo __('Options') ?></td>
    <?php endif ?>
    <td></td>
  </tr>
</thead>
</table>

<?php if ( $transaction->Order->count() > 0 || $transaction->Payments->count() > 0 ): ?>

<div id="payments">
<h3><?php echo __('Payment status') ?> :</h3>
<?php include_partial('show_payments',array('transaction' => $transaction)) ?>
</div>

<div id="details">
<h3><?php echo __('Command status') ?> :</h3>
<?php include_partial('show_details',array('transaction' => $transaction)) ?>
</div>

<?php endif ?>

<div class="clear"></div>

<?php if ( $current_transaction ): ?>
<?php include_partial('show_order') ?>
<?php endif ?>

<div id="cmd-links">
<?php include_partial('global/show_links', array('objects' => $for_links)); ?>
</div>

<?php include_partial('show_comment',array('transaction' => $transaction, 'form' => $form)) ?>

<?php include_partial('show_bottom',array('end' => $end)) ?>

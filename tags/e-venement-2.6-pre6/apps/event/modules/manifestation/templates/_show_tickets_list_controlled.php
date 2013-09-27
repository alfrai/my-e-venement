<?php
  $total = array('controlled' => array('qty' => 0, 'value' => 0), 'not_controlled' => array('qty' => 0, 'value' => 0),);
  $tickets = array('controlled' => array(), 'not_controlled' => array(),);
  if ( !isset($prices) ) $prices = $form->prices;
  foreach ( $prices as $price )
  {
    $qty = $value = array('controlled' => 0, 'not_controlled' => 0);
    foreach ( $price->Tickets as $t )
    if ( $t->printed_at || $t->integrated_at )
    {
      $key = $t->Controls->count() > 0  && !$t->hasBeenCancelled() && $t->Duplicatas->count() == 0 ? 'controlled' : 'not_controlled';
      $tickets[$key][$t->id] = $t;
      $value[$key] += $t->value;
      $qty[$key]++;
    }
    $total['controlled']['qty'] += $qty['controlled'];
    $total['controlled']['value'] += $value['controlled'];
    $total['not_controlled']['qty'] += $qty['not_controlled'];
    $total['not_controlled']['value'] += $value['not_controlled'];
  }
  sort($tickets['controlled']); sort($tickets['not_controlled']);
?>

<h2><?php echo __('Tickets to be controlled') ?></h2>
<table class="to-be-controlled" class="control">
<tbody>
  <?php $overlined = true ?>
  <?php foreach ( $tickets['not_controlled'] as $ticket ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $ticket ?></td>
    <td class="qty"><?php echo $ticket->price_name ?></td>
    <td class="price"><?php echo format_currency($ticket->value,'€') ?></td>
    <td class="transaction"><?php echo cross_app_link_to('#'.$ticket->Transaction,'tck','ticket/sell?id='.$ticket->transaction_id) ?></td>
    <td class="contact"><?php
      echo $ticket->Transaction->professional_id
        ? cross_app_link_to($c=$t->Transaction->Professional->Contact,'rp','contact/show?id='.$c->id)
          .' @ '.
          cross_app_link_to($o=$t->Transaction->Professional->Organism,'rp','organism/show?id='.$o->id)
          .' <span class="pictos">'.$t->Transaction->Professional->getRaw('groups_picto').'</span>'
        : $ticket->Transaction->contact_id
        ? cross_app_link_to($ticket->Transaction->Contact,'rp','contact/show?id='.$ticket->Transaction->Contact->id)
          .' <span class="pictos">'.$t->Transaction->Contact->getRaw('groups_picto').'</span>'
        : '';
    ?></td>
  </tr>
  <?php endforeach ?>
  <tbody>
  <?php include_partial('show_tickets_list_table_footer',array('total' => $total['not_controlled'])) ?>
  <thead>
    <tr>
      <td class="name"><?php echo __('Ticket') ?></td>
      <td class="qty"><?php echo __('Price') ?></td>
      <td class="price"><?php echo __('Value') ?></td>
      <td class="transaction"><?php echo __('Transaction') ?></td>
      <td class="contact"><?php echo __('Contact') ?></td>
    </tr>
  </thead>
</table>

<h2><?php echo __('Controlled tickets') ?></h2>
<table class="controlled" class="control">
<tbody>
  <?php $overlined = true ?>
  <?php foreach ( $tickets['controlled'] as $ticket ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $ticket ?></td>
    <td class="qty"><?php echo $ticket->price_name ?></td>
    <td class="price"><?php echo format_currency($ticket->value,'€') ?></td>
    <td class="transaction"><?php echo cross_app_link_to('#'.$ticket->Transaction,'tck','ticket/sell?id='.$ticket->transaction_id) ?></td>
    <td class="contact"><?php
      echo $ticket->Transaction->professional_id
        ? cross_app_link_to($ticket->Transaction->Professional,'rp','contact/show?id='.$ticket->Transaction->Contact->id)
        : $ticket->Transaction->contact_id
        ? cross_app_link_to($ticket->Transaction->Contact,'rp','contact/show?id='.$ticket->Transaction->Contact->id)
        : '';
    ?></td>
  </tr>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_tickets_list_table_footer',array('total' => $total['controlled'])) ?>
  <thead>
    <tr>
      <td class="name"><?php echo __('Ticket') ?></td>
      <td class="qty"><?php echo __('Price') ?></td>
      <td class="price"><?php echo __('Value') ?></td>
      <td class="transaction"><?php echo __('Transaction') ?></td>
      <td class="contact"><?php echo __('Contact') ?></td>
    </tr>
  </thead>
</table>

<h2><?php echo __('Spectators controlled') ?></h2>
<table class="to-be-controlled">
  <tbody>
  <?php $total = array('qty' => 0, 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php foreach ( $form->spectators as $spectator ): ?>
  <?php
    $transaction = $contact = $pro = array();
    foreach ( $spectator->Transactions AS $transac )
    foreach ( $transac->Tickets as $t )
    if ( $t->printed && $t->Controls->count() > 0 )
    {
      if ( !isset($contact[$transac->professional_id]) )
        $contact[$transac->professional_id] = array('value' => 0, 'prices' => array(), 'ticket-ids' => array());

      $contact[$transac->professional_id]['ticket-ids'][] = $t->id;
      $contact[$transac->professional_id]['transaction'] = $transac;
      $contact[$transac->professional_id]['pro'] = $transac->Professional;
      isset($contact[$transac->professional_id]['prices'][$t->price_id])
        ? $contact[$transac->professional_id]['prices'][$t->price_name]++
        : $contact[$transac->professional_id]['prices'][$t->price_name] = 1;
      $contact[$transac->professional_id]['value'] += $t->value;
      
      $total['qty']++;
      $total['value'] += $t->value;
    }
  ?>
  <?php foreach ( $contact as $pro ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo cross_app_link_to($spectator,'rp','contact/show?id='.$spectator->id) ?></td>
    <td class="organism"><?php echo cross_app_link_to($pro['pro']->Organism,'rp','organism/show?id='.$pro['pro']->Organism->id) ?></td>
    <td class="tickets"><?php $arr = array(); foreach ( $pro['prices'] as $key => $value ) $arr[] = $value.$key; echo implode(', ',$arr); ?></td>
    <td class="price"><?php echo format_currency($pro['value'],'€') ?></td>
    <td class="transaction">#<?php echo cross_app_link_to($pro['transaction'],'tck','ticket/sell?id='.$pro['transaction']) ?></td>
    <td class="ticket-ids">#<?php echo implode(', #',$pro['ticket-ids']) ?></td>
  </tr>
  <?php endforeach ?>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_spectators_list_table_footer',array('total' => $total)) ?>
  <?php include_partial('show_spectators_list_table_header') ?>
</table>

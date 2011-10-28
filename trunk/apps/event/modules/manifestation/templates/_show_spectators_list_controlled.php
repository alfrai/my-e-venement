<h2><?php echo __('Controlled spectators') ?></h2>
<table class="controlled">
  <tbody>
  <?php $total = array('qty' => 0, 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php foreach ( $form->spectators as $transac ): ?>
  <?php
    $transaction = $contact = $pro = array();
    $contact = array('value' => 0, 'prices' => array(), 'ticket-ids' => array());
    foreach ( $transac->Tickets as $t )
    if ( $t->printed && $t->Controls->count() > 0 )
    {
      $contact['ticket-ids'][] = $t->id;
      $contact['transaction'] = $transac;
      $contact['pro'] = $transac->Professional;
      isset($contact['prices'][$t->price_name])
        ? $contact['prices'][$t->price_name]++
        : $contact['prices'][$t->price_name] = 1;
      $contact['value'] += $t->value;

      $total['qty']++;
      $total['value'] += $t->value;
    }
  ?>
  <?php if ( $contact['ticket-ids'] ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo cross_app_link_to($transac->Contact,'rp','contact/show?id='.$tansac->contact_id) ?></td>
    <td class="organism"><?php echo cross_app_link_to($contact['pro']->Organism,'rp','organism/show?id='.$contact['pro']->Organism->id) ?></td>
    <td class="tickets"><?php $arr = array(); foreach ( $contact['prices'] as $key => $value ) $arr[] = $value.$key; echo implode(', ',$arr); ?></td>
    <td class="price"><?php echo format_currency($contact['value'],'€') ?></td>
    <td class="transaction">#<?php echo cross_app_link_to($contact['transaction'],'tck','ticket/sell?id='.$contact['transaction']) ?></td>
    <td class="ticket-ids">#<?php echo implode(', #',$contact['ticket-ids']) ?></td>
  </tr>
  <?php endif ?>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_spectators_list_table_footer',array('total' => $total)) ?>
  <?php include_partial('show_spectators_list_table_header') ?>
</table>

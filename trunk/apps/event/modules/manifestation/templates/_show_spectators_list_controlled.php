<h2><?php echo __('Controlled spectators') ?></h2>
<table class="printed">
  <tbody>
  <?php $total = array('qty' => 0, 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php $contacts = array(); ?>
  <?php foreach ( $form->spectators as $transac ): ?>
  <?php
    $transaction = $contact = $pro = array();
    foreach ( $transac->Tickets as $t )
    if ( $t->printed && $t->Controls->count() > 0 )
    {
      if ( !isset($contact[$transac->professional_id]) )
        $contact[$transac->professional_id] = array('value' => 0, 'prices' => array(), 'ticket-ids' => array());
      $contact[$transac->professional_id]['ticket-ids'][] = $t->id;
      $contact[$transac->professional_id]['transaction'] = $transac;
      $contact[$transac->professional_id]['pro'] = $transac->Professional;
      isset($contact[$transac->professional_id]['prices'][$t->price_name])
        ? $contact[$transac->professional_id]['prices'][$t->price_name]++
        : $contact[$transac->professional_id]['prices'][$t->price_name] = 1;
      $contact[$transac->professional_id]['value'] += $t->value;
      
      $total['qty']++;
      $total['value'] += $t->value;
    $contacts[$transac->contact_id] = $contact;
    }
  ?>
  <?php endforeach ?>
  <?php foreach ( $contact as $pro ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo cross_app_link_to($transac->Contact,'rp','contact/show?id='.$tansac->contact_id) ?></td>
    <td class="organism"><?php echo cross_app_link_to($pro['pro']->Organism,'rp','organism/show?id='.$pro['pro']->Organism->id) ?></td>
    <td class="tickets"><?php $arr = array(); foreach ( $pro['prices'] as $key => $value ) $arr[] = $value.$key; echo implode(', ',$arr); ?></td>
    <td class="price"><?php echo format_currency($pro['value'],'€') ?></td>
    <td class="transaction">#<?php echo cross_app_link_to($pro['transaction'],'tck','ticket/sell?id='.$pro['transaction']) ?></td>
    <td class="ticket-ids">#<?php echo implode(', #',$pro['ticket-ids']) ?></td>
  </tr>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_spectators_list_table_footer',array('total' => $total)) ?>
  <?php include_partial('show_spectators_list_table_header') ?>
</table>

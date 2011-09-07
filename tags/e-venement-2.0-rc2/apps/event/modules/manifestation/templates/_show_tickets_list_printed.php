<h2><?php echo __('Printed tickets') ?></h2>
<table class="printed">
  <tbody>
  <?php $total = array('qty' => 0, 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php foreach ( $form->prices as $price ): ?>
  <?php
    $transaction = $contact = $pro = array();
    $qty = $value = 0;
    foreach ( $price->Tickets as $t )
    if ( $t->printed )
    {
      $transaction[$t->transaction_id] = cross_app_link_to('#'.$t->transaction_id,'tck','ticket/sell?id='.$t->transaction_id);
      if ( $t->Transaction->professional_id )
        $pro[$t->Transaction->professional_id] =
          cross_app_link_to($c=$t->Transaction->Professional->Contact,'rp','contact/show?id='.$c->id).
          ' @ '.
          cross_app_link_to($o=$t->Transaction->Professional->Organism,'rp','organism/show?id='.$o->id);
      else if ( $t->Transaction->contact_id )
        $contact[$t->Transaction->contact_id] = cross_app_link_to($t->Transaction->Contact,'rp','contact/show?id='.$t->Transaction->Contact->id);
      $value += $t->value;
      $qty++;
    }
    $total['qty'] += $qty;
    $total['value'] += $value;
  ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $price ?></td>
    <td class="qty"><?php echo $qty ?></td>
    <td class="price"><?php echo format_currency($value,'€') ?></td>
    <td class="transaction"><?php echo implode('<br/>',$transaction) ?></td>
    <td class="contact"><?php echo implode('<br/>',array_merge(array_values($pro),array_values($contact))) ?></td>
  </tr>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_tickets_list_table_footer',array('total' => $total)) ?>
  <?php include_partial('show_tickets_list_table_header') ?>
</table>

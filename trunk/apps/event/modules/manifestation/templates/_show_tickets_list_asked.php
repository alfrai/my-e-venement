<h2><?php echo __('Asked tickets') ?></h2>
<table class="ordered">
<tbody>
  <?php $total = array('qty' => 0, 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php if ( !isset($prices) ) $prices = $form->prices ?>
  <?php foreach ( $prices as $price ): ?>
  <?php
  if ( !isset($price->asked) )
  {
    $qty = $value = 0;
    $transaction = $contact = $pro = array();
    foreach ( $price->Tickets as $t )
    if ( !$t->printed_at && !$t->integrated_at && $t->Transaction->Order->count() == 0 )
    {
      $transaction[$t->transaction_id] = cross_app_link_to('#'.$t->transaction_id,'tck','ticket/sell?id='.$t->transaction_id);
      if ( $t->Transaction->professional_id )
        $contact[$t->Transaction->id] =
          cross_app_link_to($c=$t->Transaction->Professional->Contact,'rp','contact/show?id='.$c->id)
          .' @ '.
          cross_app_link_to($o=$t->Transaction->Professional->Organism,'rp','organism/show?id='.$o->id)
          .' <span class="pictos">'.$t->Transaction->Professional->getRaw('groups_picto').'</span>';
      else if ( $t->Transaction->contact_id )
        $contact[$t->Transaction->id] = cross_app_link_to($t->Transaction->Contact,'rp','contact/show?id='.$t->Transaction->Contact->id)
        .' <span class="pictos">'.$t->Transaction->Contact->getRaw('groups_picto').'</span>';
      else
        $contact[$t->Transaction->id] = '&nbsp;';
      $value += $t->value;
      $qty++;
    }
  }
  else
  {
    $qty = $price->asked;
    $value = $price->asked_value;
    $transaction = $contact = array('-');
  }
  
  $total['qty'] += $qty;
  $total['value'] += $value;
  ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $price ?></td>
    <td class="qty"><?php echo $qty ?></td>
    <td class="price"><?php echo format_currency($value,'€') ?></td>
    <td class="transaction"><?php echo implode('<br/>',$transaction) ?></td>
    <td class="contact"><?php echo implode('<br/>',$contact) ?></td>
  </tr>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_tickets_list_table_footer',array('total' => $total)) ?>
  <?php include_partial('show_tickets_list_table_header') ?>
</table>

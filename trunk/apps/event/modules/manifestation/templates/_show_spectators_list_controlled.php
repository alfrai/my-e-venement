<h2><?php echo __('Controlled spectators') ?></h2>
<table class="controlled">
  <tbody>
  <?php $total = array('qty' => array(0), 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php if ( !isset($spectators) ) $spectators = $form->spectators ?>
  <?php foreach ( $spectators as $transac ): ?>
  <?php
    $transaction = $contact = $pro = array();
    $contact = array('value' => 0, 'prices' => array(), 'ticket-ids' => array());
    foreach ( $transac->Tickets as $t )
    if ( ($t->printed_at || $t->integrated_at) && $t->Controls->count() > 0 )
    {
      $contact['ticket-ids'][] = $t->id;
      $contact['transaction'] = $transac;
      $contact['pro'] = $transac->Professional;
      isset($contact['prices'][$t->price_name])
        ? $contact['prices'][$t->price_name]++
        : $contact['prices'][$t->price_name] = 1;
      $contact['value'] += $t->value;

      $total['qty'][0]++;
      $total['value'] += $t->value;
    }
  ?>
  <?php if ( $contact['ticket-ids'] ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <?php include_partial('show_spectators_list_line',array(
      'transac' => $transac,
      'contact' => $contact,
      'ws'      => $contact['prices'],
      'show_workspaces' => $show_workspaces,
      'wsid'    => 0,
    )) ?>
  </tr>
  <?php endif ?>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_spectators_list_table_footer',array('total' => $total)) ?>
  <?php include_partial('show_spectators_list_table_header') ?>
</table>

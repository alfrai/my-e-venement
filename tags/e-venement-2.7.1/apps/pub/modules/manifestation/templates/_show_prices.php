<?php use_helper('Number') ?>

<?php
  $sf_context->getEventDispatcher()->notify($event = new sfEvent($this, 'pub.before_showing_prices', array('manifestation' => $gauge->Manifestation)));
  if ( !$event->getReturnValue() )
  {
    echo $event['message'];
    return;
  }
?>

<?php
  // limitting the max quantity, especially for prices linked to member cards
  $vel = sfConfig::get('app_tickets_vel');
  $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
  if ( $gauge->Manifestation->online_limit_per_transaction && $gauge->Manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
    $vel['max_per_manifestation'] = $gauge->Manifestation->online_limit_per_transaction;
  
  // max per manifestation per contact ...
  $vel['max_per_manifestation_per_contact'] = isset($vel['max_per_manifestation_per_contact']) ? $vel['max_per_manifestation_per_contact'] : false;
  if ( $vel['max_per_manifestation_per_contact'] > 0 )
  {
    $max = $vel['max_per_manifestation_per_contact'];
    foreach ( $sf_user->getContact()->Transactions as $transaction )
    if ( $transaction->id != $sf_user->getTransaction()->id )
    foreach ( $transaction->Tickets as $ticket )
    if (( $ticket->transaction_id == $sf_user->getTransaction()->id || $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
      && !$ticket->hasBeenCancelled()
      && $gauge->Manifestation->id == $ticket->manifestation_id
    )
    {
      $vel['max_per_manifestation_per_contact']--;
    }
    $vel['max_per_manifestation'] = $vel['max_per_manifestation'] > $vel['max_per_manifestation_per_contact']
      ? $vel['max_per_manifestation_per_contact']
      : $vel['max_per_manifestation'];
  }
?>

<table class="prices">
<?php if ( $gauge->Manifestation->PriceManifestations->count() > 0 ): ?>
<tbody>
<?php foreach ( $gauge->Manifestation->PriceManifestations as $pm ): ?>
<?php if ( in_array($gauge->workspace_id, $pm->getRawValue()->Price->Workspaces->getPrimaryKeys()) ): ?>
  <?php
    // calculating the quantity of tickets already in the cart
    $qty = 0;
    foreach ( $pm->Price->Tickets as $ticket)
    if ( $ticket->gauge_id == $gauge->id )
      $qty++;
    
    // price_id & default quantity
    $form
      ->setPriceId($pm->price_id)
      ->setQuantity($qty);
    
    // limitting the max quantity, especially for prices linked to member cards
    $max = $gauge->value - $gauge->printed - $gauge->ordered - $gauge->Manifestation->online_limit - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0);
    $max = $max > $vel['max_per_manifestation'] ? $vel['max_per_manifestation'] : $max;
    
    // member cards
    if ( $pm->Price->member_card_linked )
    {
      $mc_max = 0;
      
      if ( isset($mcp[$pm->price_id]) )
      {
        $mc_max += $mcp[$pm->price_id][''] < 0 ? 0 : $mcp[$pm->price_id][''];
        if ( isset($mcp[$pm->price_id][$gauge->Manifestation->event_id]) )
          $mc_max += $mcp[$pm->price_id][$gauge->Manifestation->event_id];
      }
      
      if ( $max > $mc_max )
        $max = $mc_max;
    }
    
    if ( $max <= 0 )
    {
      echo '<tr><td class="price" colspan="4">'.__('Price %%price%% not available', array('%%price%%' => $pm->Price)).'</td></tr>';
      continue;
    }
    $form->setMaxQuantity($max);
  ?>
  <tr>
    <td class="price">
      <?php echo $pm->Price->description ? $pm->Price->description : $pm->Price ?>
      <?php echo $form->renderHiddenFields() ?>
    </td>
    <td class="value"><?php echo format_currency($pm->value,'€') ?></td>
    <td class="quantity"><?php echo $form['quantity'] ?></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
<?php endif ?>
<?php endforeach ?>
<tbody>
<?php endif ?>
<tfoot>
  <tr>
    <td class="price"></td>
    <td class="value"></td>
    <td class="quantity"></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="price"><?php echo __('Price') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="quantity"><?php echo __('Quantity') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

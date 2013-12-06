<?php use_helper('Number') ?>
<table class="prices">
<?php if ( $gauge->Manifestation->PriceManifestations->count() > 0 ): ?>
<tbody>
<?php foreach ( $gauge->Manifestation->PriceManifestations as $pm ): ?>
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
    $vel = sfConfig::get('app_tickets_vel');
    $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
    $max = $gauge->value - $gauge->printed - $gauge->ordered - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0);
    $max = $max > $vel['max_per_manifestation'] ? $vel['max_per_manifestation'] : $max;
    if ( $pm->Price->member_card_linked )
    {
      $cpt = 0;
      
      if ( isset($mcp[$pm->price_id]) )
      {
        $cpt += $mcp[$pm->price_id][''] < 0 ? 0 : $mcp[$pm->price_id][''];
        if ( isset($mcp[$pm->price_id][$gauge->Manifestation->event_id]) )
          $cpt += $mcp[$pm->price_id][$gauge->Manifestation->event_id];
      }
      
      if ( $max > $cpt )
        $max = $cpt;
    }
    
    if ( $max <= 0 )
      continue;
    
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

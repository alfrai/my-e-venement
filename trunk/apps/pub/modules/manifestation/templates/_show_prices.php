<?php $vel = sfConfig::get('app_tickets_vel') ?>
<?php if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false; ?>
<?php use_helper('Number') ?>
<?php use_helper('Slug') ?>
<table class="prices">
<?php if ( $gauge->Manifestation->PriceManifestations->count() > 0 ): ?>
<tbody>
<?php
  // WIP tickets
  $tickets = array();
  foreach ( $gauge->Tickets as $ticket )
  if ( !$ticket->price_id && $ticket->price_name )
    $tickets[] = $ticket;
?>
  <tr class="seating in-progress">
    <?php if ( $vel['full_seating_by_customer'] ): ?>
      <td class="seats" title="<?php echo $str = __('To remove a booked seat, click it on the plan') ?>">
        <?php include_partial('show_prices_seats', array('form' => $form, 'tickets' => $tickets)) ?>
      </td>
    <?php endif ?>
    <td class="price value explanation" colspan="2"><span><?php echo $str ?></span></td>
    <td class="quantity" title="<?php echo $str ?>"><?php echo count($tickets) ?></td>
    <td class="total">-</td>
  </tr>

<?php foreach ( $gauge->Manifestation->PriceManifestations as $pm ): ?>
<?php if ( in_array($gauge->workspace_id, $pm->getRawValue()->Price->Workspaces->getPrimaryKeys()) ): ?>
  <?php
    // calculating the quantity of tickets already in the cart
    $tickets = array();
    foreach ( $pm->Price->Tickets as $ticket )
    if ( $ticket->gauge_id == $gauge->id )
      $tickets[] = $ticket;
    
    // price_id & default quantity
    $form
      ->setPriceId($pm->price_id)
      ->setQuantity(count($tickets));
    
    // limitting the max quantity, especially for prices linked to member cards
    $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
    if ( $gauge->Manifestation->online_limit_per_transaction && $gauge->Manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
      $vel['max_per_manifestation'] = $gauge->Manifestation->online_limit_per_transaction;
    
    $max = $gauge->value - $gauge->printed - $gauge->ordered - $gauge->Manifestation->online_limit - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0);
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
  <tr data-price-id="<?php echo $pm->price_id ?>">
    <?php if ( $vel['full_seating_by_customer'] ): ?>
      <td class="seats">
        <?php include_partial('show_prices_seats', array('form' => $form, 'tickets' => $tickets)) ?>
      </td>
    <?php endif ?>
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
</tbody>
<?php endif ?>
<tfoot>
  <tr>
    <?php if ( $vel['full_seating_by_customer'] ): ?><td class="seats"></td><?php endif ?>
    <td class="price"></td>
    <td class="value"></td>
    <td class="quantity"></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
  <tr>
    <?php if ( $vel['full_seating_by_customer'] ): ?>
    <?php use_javascript('pub-seated-plan?'.date('Ymd'),'last') ?>
    <?php use_javascript('helper?'.date('Ymd')) ?>
    <td class="seats"></td>
    <?php endif ?>
    <td colspan="4" class="submit">
      <input type="submit" name="submit" value="<?php echo __('Cart') ?>" />
    </td>
  </td>
</tfoot>
<thead>
  <tr>
    <?php if ( $vel['full_seating_by_customer'] ): ?><td class="seats"><?php echo __('Seats') ?></td><?php endif ?>
    <td class="price"><?php echo __('Price') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="quantity"><?php echo __('Quantity') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

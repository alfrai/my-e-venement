<?php
  if ( !isset($manifestation) && isset($form) )
    $manifestation = $form->getObject();
  
  foreach ( array('demands', 'orders', 'sells') as $field )
  if ( !isset($manifestation->$field) )
    $manifestation->$field = 0;
?>
<div class="gauge">
<div class="backup" id="gauge-<?php echo $manifestation->id ?>">
<p class="text">
  <span class="total"><?php echo __('Total: ',null,'gauge') ?><span class="nb"><?php echo $gauge->value ?></span></span>
  <span class="free"><?php echo __('Free: ',null,'gauge') ?><span class="nb"><?php echo ($gauge->value - ((sfConfig::get('project_tickets_count_demands',false) ? $manifestation->demands : 0)+$manifestation->orders+$manifestation->sells)) ?></span></span>
  <br/>
  <span class="sells"><?php echo __('Sells: ',null,'gauge') ?><span class="nb"><?php echo intval($manifestation->sells) ?></span></span>
  <?php if ( $sf_user->hasCredential('tck-accounting-order') ): ?>
    <span class="orders"><?php echo __('Orders: ',null,'gauge') ?><span class="nb"><?php echo intval($manifestation->orders) ?></span></span>
  <?php endif ?>
  <?php if ( sfConfig::get('project_transaction_count_demands',false) ): ?>
  <span class="asks"><?php echo __('Demands: ',null,'gauge')?><span class="nb"><?php echo intval($manifestation->demands) ?></span></span>
  <?php endif ?>
</p>
<div>
<input type="hidden" name="gauge-id" value="<?php echo $manifestation->id ?>" />
<?php
  $area = 30000; // width: 100%, height: 100%
  $seat = sqrt($area / ($gauge->value > 0 ? $gauge->value : 100));
  $free = $gauge->value - $manifestation->sells - $manifestation->orders - (sfConfig::get('project_transaction_count_demands',false) ? $manifestation->demands : 0);
?>
<?php if ( $gauge->value < (($tmp = intval(sfConfig::get('app_gauge_hide_graphical_display_until'))) ? $tmp : 10000) ): ?>
<?php
  // cell's size
  $px = round($seat);
  if ( $gauge->value > 1750 && $px > 1 )
    $px--;
  
  $occ = 0;
?>
<?php  for ( $i = 0 ; $i < $free ; $i++ ): $occ++; ?><div
  class="seat free"
  style="width: <?php echo $px ?>px; height: <?php echo $px ?>px;" <?php /* echo $seat ?>%; height: <?php echo $seat ?>%;" */ ?>
  ></div
><?php endfor ?>
<?php if ( sfConfig::get('project_transaction_count_demands',false) ): ?>
<?php  for ( $i = 0 ; $i < $manifestation->demands ; $i++ ): $occ++; ?><div
  class="seat demands <?php echo $occ <= $gauge->value ? 'free' : 'overquota' ?>"
  style="width: <?php echo $px ?>px; height: <?php echo $px ?>px;" <?php /* echo $seat ?>%; height: <?php echo $seat ?>%;" */ ?>
  ></div
><?php endfor ?>
<?php endif ?>
<?php  for ( $i = 0 ; $i < $manifestation->orders ; $i++ ): $occ++; ?><div
  class="seat orders <?php echo $occ <= $gauge->value ? '' : 'overquota' ?>"
  style="width: <?php echo $px ?>px; height: <?php echo $px ?>px;" <?php /* echo $seat ?>%; height: <?php echo $seat ?>%;" */ ?>
  ></div
><?php endfor ?>
<?php  for ( $i = 0 ; $i < $manifestation->sells; $i++ ): $occ++; ?><div
  class="seat sells <?php echo $occ <= $gauge->value ? '' : 'overquota' ?>"
  style="width: <?php echo $px ?>px; height: <?php echo $px ?>px;" <?php /* echo $seat ?>%; height: <?php echo $seat ?>%;" */ ?>
  ></div
><?php endfor ?>
<?php endif ?>
</div>
</div>
</div>

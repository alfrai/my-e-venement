<div class="gauge">
<p class="text">
  <span class="total"><?php echo __('Total: ',null,'gauge') ?><span class="nb"><?php echo $gauge->value ?></span></span>
  <span class="free"><?php echo __('Free: ',null,'gauge') ?><span class="nb"><?php echo ($gauge->value - ($manifestation->demands+$manifestation->orders+$manifestation->sells)) ?></span></span>
  <br/>
  <span class="sells"><?php echo __('Sells: ',null,'gauge') ?><span class="nb"><?php echo $manifestation->sells ?></span></span>
  <?php if ( $sf_user->hasCredential('tck-accounting-order') ): ?>
    <span class="orders"><?php echo __('Orders: ',null,'gauge') ?><span class="nb"><?php $manifestation->orders ?></span></span>
  <?php endif ?>
  <span class="asks"><?php echo __('Demands: ',null,'gauge')?><span class="nb"><?php echo $manifestation->demands ?></span></span>
</p>
<div>
<input type="hidden" name="gauge-id" value="<?php echo $manifestation->id ?>" />
<?php
  $area = 80*80; // width: 100%, height: 100%
  $seat = sqrt($area / ($gauge->value > 0 ? $gauge->value : 100) );
  $manifestation->sells;
  $free = $gauge->value - $manifestation->sells - $manifestation->orders - $manifestation->demands;
  
  $occ = 0;
?>
<?php  for ( $i = 0 ; $i < $free ; $i++ ): $occ++; ?><div
  class="seat free"
  style="width: <?php echo $seat ?>%; height: <?php echo $seat ?>%;"
  title="<?php echo __('%%n%% free seats',array('%%n%%' => $free)) ?>"></div
><?php endfor ?>
<?php  for ( $i = 0 ; $i < $manifestation->demands ; $i++ ): $occ++; ?><div
  class="seat demands <?php echo $occ <= $gauge->value ? 'free' : 'overquota' ?>"
  style="width: <?php echo $seat ?>%; height: <?php echo $seat ?>%;"
  title="<?php echo __('%%n%% demanded tickets',array('%%n%%' => $manifestation->demands)) ?>"></div
><?php endfor ?>
<?php  for ( $i = 0 ; $i < $manifestation->orders ; $i++ ): $occ++; ?><div
  class="seat orders <?php echo $occ <= $gauge->value ? '' : 'overquota' ?>" style="width: <?php echo $seat ?>%; height: <?php echo $seat ?>%;"
  title="<?php echo __('%%n%% prebooked seats',array('%%n%%' => $manifestation->orders)) ?>"></div
><?php endfor ?>
<?php  for ( $i = 0 ; $i < $manifestation->sells; $i++ ): $occ++; ?><div
  class="seat sells <?php echo $occ <= $gauge->value ? '' : 'overquota' ?>" style="width: <?php echo $seat ?>%; height: <?php echo $seat ?>%;"
  title="<?php echo __('%%n%% booked tickets',array('%%n%%' => $manifestation->sells)) ?>"></div
><?php endfor ?>
</div>
</div>

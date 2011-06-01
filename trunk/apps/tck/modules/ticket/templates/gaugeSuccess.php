<div class="gauge" style="border: 1px solid black;"><div>
<input type="hidden" name="gauge-id" value="<?php echo $manifestation->id ?>" />
<?php
  $area = 80*80; // width: 100%, height: 100%
  $seat = sqrt($area / $gauge->value);
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
</div></div>


<!--
<div id="gauge-<?php echo $manifestation->id ?>" class="gauge-id">
  <input type="hidden" name="gauge-id" value="<?php echo $manifestation->id ?>"/>
  <p class="sells"
       style="height: <?php echo $height['sells'] ?>%;"
       title="<?php echo __('%%n%% sold seats',array('%%n%%' => $manifestation->sells)) ?>">
    <span><?php echo __('%%n%% sold seats',array('%%n%%' => $manifestation->sells)) ?></span>
  </p>
  <p class="orders"
       style="height: <?php echo $height['orders'] ?>%;"
       title="<?php echo __('%%n%% ordered seats',array('%%n%%' => $manifestation->orders)) ?>">
    <span><?php echo __('%%n%% ordered seats',array('%%n%%' => $manifestation->orders)) ?></span>
  </p>
  <p class="demands"
       style="height: <?php echo $height['demands'] ?>%;"
       title="<?php echo __('%%n%% demanded seats',array('%%n%%' => $manifestation->demands)) ?>">
    <span><?php echo __('%%n%% demanded seats',array('%%n%%' => $manifestation->demands)) ?></span>
  </p>
  <p class="free"
       style="height: <?php echo $height['free'] ?>%;"
       title="<?php echo __('%%n%% free seats',array('%%n%%' => $gauge->value - $manifestation->orders - $manifestation->sells)) ?>">
    <span><?php echo __('%%n%% free seats',array('%%n%%' => $gauge->value - $manifestation->orders - $manifestation->sells)) ?></span>
  </p>
</div>
</div>
-->

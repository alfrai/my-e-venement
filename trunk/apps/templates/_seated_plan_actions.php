<?php
  if ( isset($gauge) && $gauge->getRawValue() instanceof Gauge )
    $param = 'gauge_id='.$gauge->id;
  elseif ( isset($gauges) && count($gauges) > 0 )
  {
    $ids = array();
    foreach ( $gauges as $gauge )
      $ids[] = $gauge->id;
    $param = 'gauges_list[]='.implode('&amp;gauges_list[]=', $ids);
  }
?>
          <a class="occupation"
             href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent').find('.seated-plan'); LI.seatedPlanLoadData($(this).prop('href'), plan); return false;"
          >
            <?php echo __('Show occupation') ?>
          </a>
          <?php use_javascript('event-seated-plan-more-data') ?>
          <a class="shortnames"
             href="<?php echo url_for('seated_plan/getShortnames?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Show short names') ?>
          </a>
          <a class="ranks"
             href="<?php echo url_for('seated_plan/getRanks?id='.$seated_plan->id) ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Show ranks') ?>
          </a>

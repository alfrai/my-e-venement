<?php
  $plans = array();
  foreach ( $manifestation->getRawValue()->Gauges as $gauge )
  if ( $gauge->online )
  {
    $sp = $gauge->seated_plan;
    if ( !isset($plans[$sp->picture_id]) )
      $plans[$sp->picture_id] = array(
        'seated_plan' => $sp,
        'gauges' => array(),
      );
    $plans[$sp->picture_id]['gauges'][] = $gauge;
  }
?>

<?php use_stylesheet('/private/event-seated-plan') ?>
<div id="plans">
<?php foreach ( $plans as $plan ): ?>
<?php if ( isset($plan['seated_plan']) && $plan['seated_plan'] instanceof SeatedPlan ): ?>
  <div class="plan-<?php echo $plan['seated_plan']->id ?> gauge">
    <?php include_partial('global/magnify') ?>
    <?php echo $plan['seated_plan']->render($plan['gauges'], array(
      'app' => 'pub',
      'get-seats' => 'seats/index',
      'match-seated-plan' => false,
    )) ?>
  </div>
<?php endif ?>
<?php endforeach ?>
</div>

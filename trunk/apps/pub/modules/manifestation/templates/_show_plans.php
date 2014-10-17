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
<?php use_javascript('jquery.overscroll.min.js') ?>
<div id="plans-loading">
  <?php echo sfConfig::get('app_texts_seated_plan_loading', __('Seated plan loading, thanks for your patience...')); ?>
</div>
<div id="plans" data-manifestation-id="<?php echo $manifestation->id ?>" class="gauge">
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
  <div class="infos">
    <a href="<?php echo url_for('ticket/modTickets?manifestation_id='.$manifestation->id) ?>" id="ajax-init-data"></a>
    <a href="<?php echo url_for('cart/show') ?>" id="go-to-cart"></a>
    <span class="no-price"><?php echo __('Ticket to remove or price to set') ?></span>
  </div>
</div>

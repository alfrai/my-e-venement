<div class="sf_admin_form_row sf_admin_form_field_show_picture">
<?php
  $plans = array();
  $manifestation = $form->getObject()->Manifestation;
  foreach ( $manifestation->Gauges as $gauge )
  {
    $sp = $gauge->seated_plan;
    if (! $sp instanceof SeatedPlan )
      continue;
    
    if ( !isset($plans[$sp->picture_id]) )
      $plans[$sp->picture_id] = array(
        'seated_plan' => $sp,
        'gauges' => array(),
      );
    $plans[$sp->picture_id]['gauges'][] = $gauge;
  }
?>
<div class="seated-plan-parent" id="plans" data-manifestation-id="<?php echo $manifestation->id ?>">
  <?php foreach ( $plans as $plan ): ?>
  <?php if ( isset($plan['seated_plan']) && $plan['seated_plan'] instanceof SeatedPlan ): ?>
    <?php include_partial('global/magnify') ?>
    <div class="plan-<?php echo $plan['seated_plan']->id ?> plan">
      <?php echo $plan['seated_plan']->render($plan['gauges'], array(
        'match-seated-plan' => false,
        'add-data-src'      => true,
        'action'            => 'seated_plan/getHoldSeats',
        'hold-id'           => $form->getObject()->id,
        //'on-demand' => true,
      )) ?>
    </div>
  <?php endif ?>
  <?php endforeach ?>
  <a href="<?php echo url_for('hold/linkSeat?id='.$form->getObject()->id.'&seat_id=PUT_SEATID_HERE') ?>"
     data-replace="PUT_SEATID_HERE"
     id="link-seat"
     style="display: none;"
  ></a>
</div>
<div class="label ui-helper-clearfix">
  <div class="help">
    <span class="ui-icon ui-icon-help floatleft"></span>
    <?php echo __('Clicking a on a free seat adds it into this hold. Clicking on a held seat removes it from this hold. CTRL+Click+Drag allows to select/deselect multiple seats in batch.') ?>
  </div>
</div>

</div>

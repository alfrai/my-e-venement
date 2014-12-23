<?php use_javascript('jquery.overscroll.min.js') ?>
<?php use_stylesheet('gauge') ?>
<?php
  $plans = array();
  foreach ( $manifestation->getRawValue()->Gauges as $gauge )
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

<div class="sf_admin_form_row sf_admin_field_workspaces_list">
  <label><?php echo __('Workspaces list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    
    <!-- all gauges merged -->
    <?php if ( $form->getObject()->Gauges->count() == 0 ): ?>
      <li><?php echo __('No registered workspace') ?></li>
    <?php else: if ( $form->getObject()->Gauges->count() > 1 ): ?>
    <li class="ui-corner-all gauge gauges-all"
        title="<?php echo __('If a seated plan exists, it will show up if you click on the gauge') ?>"
        data-manifestation-id="<?php echo $form->getObject()->id ?>"
    >
      <span><?php echo __('Merging workspaces') ?></span>
      <a class="gauge-gfx" href="<?php echo cross_app_url_for('tck','ticket/gauge?id='.$form->getObject()->id.'&wsid=all') ?>">gauge</a>
      <div class="seated-plan-parent" id="plans" data-manifestation-id="<?php echo $manifestation->id ?>">
      <?php foreach ( $plans as $plan ): ?>
      <?php if ( isset($plan['seated_plan']) && $plan['seated_plan'] instanceof SeatedPlan ): ?>
        <?php include_partial('global/magnify') ?>
        <div class="seated-plan-actions">
          <?php include_partial('global/seated_plan_actions', array('gauges' => $plan['gauges'], 'seated_plan' => $plan['seated_plan'])) ?>
        </div>
        <div class="plan-<?php echo $plan['seated_plan']->id ?> plan">
          <?php echo $plan['seated_plan']->render($plan['gauges'], array(
            'match-seated-plan' => false,
            'add-data-src' => true,
            'on-demand' => true,
          )) ?>
        </div>
      <?php endif ?>
      <?php endforeach ?>
      </div>
    </li>
    <?php endif; ?>
    <?php
      $gauges = array();
      foreach ( $form->getObject()->Gauges as $gauge )
        $gauges[$gauge->Workspace->name.'-'.$gauge->id] = $gauge;
      ksort($gauges);
    ?>
    
    <!-- gauges one by one -->
    <?php foreach ( $gauges as $gauge ): ?>
    <li class="ui-corner-all gauge"
        data-gauge-id="<?php echo $gauge->id ?>"
        title="<?php echo __('If a seated plan exists, it will show up if you click on the gauge') ?>"
    >
      <a href="<?php echo url_for('workspace/show?id='.$gauge->Workspace->id) ?>"><?php echo $gauge->Workspace ?></a>
      (<?php echo $gauge->online ? __('Online') : '' ?>)
      <a class="gauge-gfx" href="<?php echo cross_app_url_for('tck','ticket/gauge?id='.$form->getObject()->id.'&wsid='.$gauge->Workspace->id) ?>">gauge</a>
      <?php if ( $gauge->Workspace->seated && $seated_plan = $form->getObject()->Location->getWorkspaceSeatedPlan($gauge->workspace_id) ): ?>
      <div class="seated-plan-parent" title="">
        <?php include_partial('global/magnify') ?>
        <div class="seated-plan-actions">
          <?php include_partial('global/seated_plan_actions', array('gauge' => $gauge, 'seated_plan' => $seated_plan)) ?>
        </div>
        <a class="picture seated-plan on-demand" href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id) ?>" style="background-color: <?php echo $seated_plan->background ?>;">
          <?php use_stylesheet('/private/event-seated-plan?'.date('Ymd')) ?>
          <?php echo $seated_plan->Picture->getHtmlTag(array('title' => $seated_plan->Picture, 'width' => $seated_plan->ideal_width)) ?>
        </a>
      </div>
      <?php endif ?>
    </li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
  <script type="text/javascript">
    if ( LI == undefined )
      LI = {};
    
    LI.manifestation_gauge_gfx = function()
    {
      if( $('a.gauge-gfx').length > 0 )
      {
        var gauge = $('a.gauge-gfx:first');
        $.get(gauge.prop('href'),function(data){
          var new_gauge = $($.parseHTML(data)).find('.gauge');
          gauge.replaceWith(new_gauge);
          
          // seated plans
          new_gauge.click(function(){
            $('.sf_admin_field_workspaces_list .gauge').removeClass('active');
            $(this).parent().closest('.gauge').addClass('active');
            $(this).parent().closest('.gauge').find('.seated-plan-actions a:first').click();
          });
          
          // next
          LI.manifestation_gauge_gfx();
        });
      }
    }
    $(document).ready(function(){
      LI.manifestation_gauge_gfx();
    });
  </script>
</div>

<?php use_javascript('jquery.overscroll.min.js') ?>
<?php use_stylesheet('gauge') ?>
<?php use_stylesheet('event-gauge') ?>
<?php
  $plans = array();
  $gauges = Doctrine::getTable('Gauge')->createQuery('g', false)
    ->leftJoin('g.Manifestation m')
    ->leftJoin('m.Location l')
    ->leftJoin('l.SeatedPlans sp')
    ->leftJoin('sp.Picture p')
    ->leftJoin('sp.Workspaces ws WITH ws.id = g.workspace_id')
    ->select('g.*, m.*, l.*, sp.*, ws.*, p.id, p.name')
    ->andWhere('g.manifestation_id = ?', $form->getObject()->id)
    ->execute();
  foreach ( $gauges as $gauge )
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
    <?php if ( $gauges->count() == 0 ): ?>
      <li><?php echo __('No registered workspace') ?></li>
    <?php else: if ( $gauges->count() > 1 ): ?>
    <li class="ui-corner-all gauge gauges-all"
        title="<?php echo __('If a seated plan exists, it will show up if you click on the gauge') ?>"
        data-manifestation-id="<?php echo $form->getObject()->id ?>"
    >
      <span><?php echo __('Global gauge') ?></span>
      <a class="gauge-gfx" href="<?php echo url_for('gauge/state?manifestation_id='.$form->getObject()->id.'&json=true') ?>">gauge</a>
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
      $arr_gauges = array();
      foreach ( $gauges as $gauge )
        $arr_gauges[$gauge->Workspace->name.'-'.$gauge->id] = $gauge;
      ksort($arr_gauges);
    ?>
    
    <!-- gauges one by one -->
    <?php foreach ( $arr_gauges as $gauge ): ?>
    <li class="ui-corner-all gauge"
        data-gauge-id="<?php echo $gauge->id ?>"
        title="<?php echo __('If a seated plan exists, it will show up if you click on the gauge') ?>"
    >
      <a href="<?php echo url_for('workspace/show?id='.$gauge->Workspace->id) ?>"><?php echo $gauge->Workspace ?></a>
      <?php if ( $gauge->online || !$gauge->onsite ): $arr = array()?>
        <?php if ( $gauge->online ) $arr[] = __('Online') ?>
        <?php if ( !$gauge->onsite ) $arr[] = __('Closed on site') ?>
        (<?php echo implode(', ', $arr) ?>)
      <?php endif ?>
      <a class="gauge-gfx" href="<?php echo url_for('gauge/state?id='.$gauge->id.'&json=true') ?>">gauge</a>
      <?php if ( $gauge->Workspace->seated && ($seated_plan = $gauge->seated_plan) ): ?>
      <div class="seated-plan-parent" title="">
        <?php include_partial('global/magnify') ?>
        <div class="seated-plan-actions">
          <?php include_partial('global/seated_plan_actions', array('gauge' => $gauge, 'seated_plan' => $seated_plan)) ?>
        </div>
        <a class="picture seated-plan on-demand" href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id) ?>" style="background-color: <?php echo $seated_plan->background ?>;">
          <?php use_stylesheet('/private/event-seated-plan?'.date('Ymd'), 'last', array('media' => 'all')) ?>
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
      if ( $('a.gauge-gfx:not(.done)').length == 0 )
        return;
      
      var gauge = $('a.gauge-gfx:not(.done):first');
      $.get(gauge.prop('href'),function(data){
        LI.renderGauge(JSON.stringify(data), false, gauge.parent());
        gauge.addClass('done');
        
        // seated plans
        gauge.parent().find('.gauge.raw').click(function(){
          $('.sf_admin_field_workspaces_list .gauge').removeClass('active');
          $(this).parent().closest('.gauge').addClass('active');
          $(this).parent().closest('.gauge').find('.seated-plan-actions .occupation').click();
        });
        
        // next
        LI.manifestation_gauge_gfx();
      });
    }
    $(document).ready(function(){
      LI.manifestation_gauge_gfx();
    });
  </script>
</div>

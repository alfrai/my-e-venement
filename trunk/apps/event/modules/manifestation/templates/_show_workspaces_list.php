<?php use_stylesheet('gauge') ?>
<div class="sf_admin_form_row sf_admin_field_workspaces_list">
  <label><?php echo __('Workspaces list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $form->getObject()->Gauges->count() == 0 ): ?>
      <li><?php echo __('No registered workspace') ?></li>
    <?php else: if ( $form->getObject()->Gauges->count() > 1 ): ?>
    <li class="ui-corner-all">
      <span><?php echo __('Merging workspaces') ?></span>
      <a class="gauge-gfx" href="<?php echo cross_app_url_for('tck','ticket/gauge?id='.$form->getObject()->id.'&wsid=all') ?>">gauge</a>
    </li>
    <?php endif; ?>
    <?php
      $gauges = array();
      foreach ( $form->getObject()->Gauges as $gauge )
        $gauges[$gauge->Workspace->name.'-'.$gauge->id] = $gauge;
      ksort($gauges);
    ?>
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
          <?php echo $seated_plan->Picture->getHtmlTag(array('title' => $seated_plan->Picture)) ?>
        </a>
      </div>
      <?php endif ?>
    </li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
  <script type="text/javascript">
    function manifestation_gauge_gfx()
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
          manifestation_gauge_gfx();
        });
      }
    }
    $(document).ready(function(){
      manifestation_gauge_gfx();
    });
  </script>
</div>

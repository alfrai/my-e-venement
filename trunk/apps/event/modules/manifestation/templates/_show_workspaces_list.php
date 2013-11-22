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
    <?php foreach ( $form->getObject()->Gauges as $gauge ): ?>
    <li class="ui-corner-all">
      <a href="<?php echo url_for('workspace/show?id='.$gauge->Workspace->id) ?>"><?php echo $gauge->Workspace ?></a>
      (<?php echo $gauge->online ? __('Online') : '' ?>)
      <a class="gauge-gfx" href="<?php echo cross_app_url_for('tck','ticket/gauge?id='.$form->getObject()->id.'&wsid='.$gauge->Workspace->id) ?>">gauge</a>
      <?php if ( $gauge->Workspace->seated ): ?>
        <?php $seated_plan = NULL; foreach ( $gauge->Workspace->SeatedPlans as $sp ) if ( $sp->location_id == $form->getObject()->location_id ) { $seated_plan = $sp; break; } ?>
        <?php if ( $seated_plan ): ?>
          <span class="picture seated-plan" style="background-color: <?php echo $seated_plan->background ?>;">
            <?php echo $seated_plan->Picture->getHtmlTag(array('title' => $seated_plan->Picture)) ?>
            <div class="anti-handling"></div>
          </span>
          <a class="json" href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id) ?>" style="display: none"></a>
        <?php endif ?>
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
        $.get($('a.gauge-gfx:first').attr('href'),function(data){
          $('a.gauge-gfx:first').replaceWith($($.parseHTML(data)).find('.gauge'));
          manifestation_gauge_gfx();
        });
      }
    }
    function manifestation_seated_plan()
    {
      $('.picture.seated-plan img').load(function(){
        var plan;
        $.get(plan = $(this).closest('.picture.seated-plan').find('a.json').prop('href'),function(data){
          $(data).each(function(){
            this.object = plan;
            seated_plan_mouseup(this);
          });
        });
      });
    }
    $(document).ready(function(){
      manifestation_gauge_gfx();
      manifestation_seated_plan();
    });
  </script>
</div>

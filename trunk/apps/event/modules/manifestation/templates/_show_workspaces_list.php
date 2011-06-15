<?php use_stylesheet('gauge') ?>
<div class="sf_admin_form_row sf_admin_field_workspaces_list">
  <label><?php echo __('Workspaces list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Gauges->count() == 0 ): ?>
      <li><?php echo __('No registered workspace') ?></li>
    <?php else: ?>
    <li class="ui-corner-all">
      <span><?php echo __('Merging workspaces') ?></span>
      <a class="gauge-gfx" href="<?php echo cross_app_url_for('tck','ticket/gauge?id='.$manifestation->id.'&wsid=all') ?>">gauge</a>
    </li>
    <?php foreach ( $manifestation->Gauges as $gauge ): ?>
    <li class="ui-corner-all">
      <a href="<?php echo url_for('workspace/show?id='.$gauge->Workspace->id) ?>"><?php echo $gauge->Workspace ?></a>
      (<?php echo $gauge->online ? __('Online') : '' ?>)
      <a class="gauge-gfx" href="<?php echo cross_app_url_for('tck','ticket/gauge?id='.$manifestation->id.'&wsid='.$gauge->Workspace->id) ?>">gauge</a>
    </li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
  <script type="text/javascript">
    $(document).ready(function(){
      $('a.gauge-gfx').each(function(){
        $.get($(this).attr('href'),function(data){
          $('a.gauge-gfx').replaceWith($(data).find('.gauge'));
        });
      });
    });
  </script>
</div>

<div class="sf_admin_form_row sf_admin_field_workspaces_list">
  <label><?php echo __('Workspaces list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Gauges->count() == 0 ): ?>
      <li><?php echo __('No registered workspace') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Gauges as $gauge ): ?>
    <li class="ui-corner-all">
      <a href="<?php echo url_for('workspace/show?id='.$gauge->Workspace->id) ?>">
        <?php echo $gauge->Workspace ?>
      </a>
      <ul>
        <li><?php echo $gauge->value ?> pl.</li>
        <li><?php echo __('Online') ?>: <?php echo $gauge->online ? image_tag('/sfDoctrinePlugin/images/tick.png') : image_tag('/sfDoctrinePlugin/images/delete.png') ?></li>
      </ul>
    </li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>

<div class="sf_admin_form_row sf_admin_field_workspaces_list">
  <label><?php echo __('Workspaces list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Workspaces->count() == 0 ): ?>
      <li><?php echo __('No registered workspace') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Workspaces as $workspace ): ?>
    <li class="ui-corner-all"><a href="<?php echo url_for('workspace/show?id='.$workspace->id) ?>"><?php echo $workspace ?></a></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>

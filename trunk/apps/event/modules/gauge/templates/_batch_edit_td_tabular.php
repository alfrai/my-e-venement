<?php
  $g = new Gauge();
  $g->id = $gauge->getRaw('id');
  $g->workspace_id = $gauge->getRaw('workspace_id');
  $g->manifestation_id = $gauge->getRaw('manifestation_id');
  $g->value = $gauge->getRaw('value');
  $g->online = $gauge->getRaw('online');
  $g->group_name = $gauge->getRaw('group_name');
?>
<td class="sf_admin_text sf_admin_list_td_join_name">
  <?php
    $form = new GaugeForm($g);
    $form->setHidden();
    $form->setHidden(array('online', 'value'));
  ?>
  <form action="<?php echo url_for('gauge/update?id='.$g->id) ?>" method="post" title="<?php echo __("This field is updated automagically") ?>">
  <input name="sf_method" value="put" type="hidden">
  <?php foreach ( $form as $field ) echo $field; ?>
  </form>
</td>
<td class="sf_admin_text sf_admin_list_td_unjoin">
  <button class="fg-button-mini fg-button ui-state-default fg-button-icon-left" name="unjoin" value="" title="<?php echo __('Unjoin?') ?>">
    <span class="ui-icon ui-icon-circle-close"></span>
  </button>
</td>
<td class="sf_admin_text sf_admin_list_td_Workspace">
  <?php echo $gauge->Workspace ?>
</td>
<td class="sf_admin_text sf_admin_list_td_Gauge object-<?php echo $gauge->id ?>">
  <?php
    $form = new GaugeForm($g);
    $form->setHidden();
    $form->setHidden(array('online', 'group_name'));
    $form['value']->getWidget()->setLabel('');
  ?>
  <form action="<?php echo url_for('gauge/update?id='.$g->id) ?>" method="post" title="<?php echo __("This field is updated automagically") ?>">
  <input name="sf_method" value="put" type="hidden">
  <?php foreach ( $form as $field ) echo $field; ?>
  </form>
</td>
<td class="sf_admin_text sf_admin_list_td_online">
  <?php
    $form = new GaugeForm($g);
    $form->setHidden();
    $form->setHidden(array('value', 'group_name'));
  ?>
<form action="<?php echo url_for('gauge/update?id='.$g->id) ?>" method="post" title="<?php echo __("This field is updated automagically") ?>">
  <input name="sf_method" value="put" type="hidden">
  <?php foreach ( $form as $field ) echo $field; ?>
</form>
</td>

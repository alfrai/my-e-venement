<div class="sf_admin_form_row sf_admin_form_direct_action_refresh <?php echo $type ?>">

<label><?php echo $label ?>:</label>
<br/>
<button
  name="refresh_<?php echo $type ?>"
  value="<?php echo url_for($type.'/groupList?id='.$form->getObject()->id) ?>"
  onclick="javascript: group_<?php echo $type ?>s_load(); return false;"
><?php echo __('Refresh') ?></button>

</div>

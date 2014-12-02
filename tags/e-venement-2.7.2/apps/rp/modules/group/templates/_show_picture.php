<?php if ( !isset($group) || isset($group) && is_object($sf_data->getRaw('group')->Picture) && !$group->Picture->isNew() ): ?>
<div class="sf_admin_form_row <?php if ( !isset($group) ): ?>sf_admin_boolean sf_admin_form_field_show_picture<?php endif ?>">
  <label for="group_show_picture"><?php echo isset($group) ? __('Picture').':' : '' ?></label>
  <span class="picture"><?php echo $form->getObject()->Picture->getHtmlTag(array('title' => $form->getObject()->Picture)) ?></span>
</div>
<?php endif ?>

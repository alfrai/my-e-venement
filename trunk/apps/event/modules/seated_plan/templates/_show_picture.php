<?php if ( isset($form) && !$form->getObject()->Picture->isNew()
        || isset($seated_plan) && is_object($sf_data->getRaw('seated_plan')->Picture) && !$seated_plan->Picture->isNew() ): ?>
<div class="sf_admin_form_row <?php if ( !isset($seated_plan) ): ?>sf_admin_boolean sf_admin_form_field_show_picture<?php endif ?>">
  <?php if ( isset($form) ): ?>
  <div class="tools ui-corner-all">
    <div><label><?php echo __('Regexp') ?></label><input type="text" name="yummy" class="regexp" value="^[A-Za-z\-\.\/]*" /></div>
    <div><label><?php echo __('Hop') ?></label><input type="text" name="yummy" class="hop" value="1" /></div>
    <div><label><?php echo __('Do not ask') ?></label><input type="checkbox" name="yummy" class="donotask" value="1" /></div>
  </div>
  <?php endif ?>
  <div class="label ui-helper-clearfix"><label for="group_show_picture"><?php echo __('Picture').':' ?></label></div>
  <span class="picture">
    <?php echo $form->getObject()->Picture->getHtmlTag(array('title' => $form->getObject()->Picture)) ?>
    <div class="anti-handling"></div>
  </span>
</div>
<?php endif ?>

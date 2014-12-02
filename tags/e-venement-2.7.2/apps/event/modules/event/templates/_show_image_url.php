<div class="sf_admin_form_row sf_admin_field_description">
<label><?php echo __('Image URL:') ?></label>
<div class="rich-content ui-corner-all ui-widget-content">
  <?php if ( $form->getObject()->image_url ): ?>
    <a target="_blank" href="<?php echo $form->getObject()->image_url ?>"><?php echo $form->getObject()->image_url ?></a>
  <?php else: ?>
    &nbsp;
  <?php endif ?>
</div>
</div>

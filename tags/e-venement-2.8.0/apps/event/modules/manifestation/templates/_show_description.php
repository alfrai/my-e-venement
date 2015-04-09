<div class="sf_admin_form_row sf_admin_form_field_description">
  <?php if ( $v = $form->getObject()->current_version ): ?>
    <span class="diff">
      <label><?php echo __('Memo') ?>:</label>
      <?php echo nl2br($v->description) ?>
    </span>
    <label><?php echo __('Current memo') ?>:</label>
    <?php echo nl2br($manifestation->description) ?>
  <?php else: ?>
    <label><?php echo __('Memo') ?>:</label>
    <div><?php echo nl2br($manifestation->description) ?></div>
  <?php endif ?>
</div>

<div class="sf_admin_form_row sf_admin_foreignkey">
  <label for="manifestation_color_id"><?php echo __('Clear all prices') ?></label>
  <div class="label ui-helper-clearfix"></div>
  <div class="widget">
    <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"
      href="<?php echo url_for('manifestation/clearPrices?id='.$form->getObject()->id) ?>"
      onclick="javascript: return confirm('<?php echo __('Are you sure?', null, 'sf_admin') ?>');">
      <span class="ui-icon ui-icon-squaresmall-minus"></span>
      <?php echo __('Clear') ?>
    </a>
  </div>
</div>

<div class="sf_admin_form_row sf_admin_form_field_event">
  <label><?php echo __('Online limit') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
  <span class="diff">
    <?php echo $v->online_limit ?>
  </span>
  <?php endif ?>
  <?php echo $manifestation->online_limit ?>
</div>

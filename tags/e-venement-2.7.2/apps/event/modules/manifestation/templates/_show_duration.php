<div class="sf_admin_form_row sf_admin_form_field_duration">
  <label><?php echo __('Duration') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
    <span class="diff">
      <?php echo floor($v->duration/3600).':'.str_pad(floor($v->duration%3600/60), 2, '0', STR_PAD_LEFT) ?>
    </span>
  <?php endif ?>
  <?php echo $manifestation->getDurationHR() ?>
</div>

<?php if ( $object->Groups->count() > 0 ): ?>
<span class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
  <?php if ( $show_labels ): ?>
  <label><?php echo __('Remarkable') ?></label>
  <?php endif ?>
  <?php echo $object->getRaw('groups_picto') ?>
</span>
<?php endif ?>


<?php if ( $control->res == 'success' ): ?>
<img class="success" alt="<?php echo __('Ok') ?>" title="<?php echo __('Ok') ?>" src="/sfDoctrinePlugin/images/tick.png" />
<?php else: ?>
<img class="failure" alt="<?php echo __('Error') ?>" title="<?php echo __('Error') ?>" src="/sfDoctrinePlugin/images/delete.png" />
<?php endif ?>

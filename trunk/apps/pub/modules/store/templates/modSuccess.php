<?php $json = $sf_data->getRaw('json') ?>
<?php $json['success']['message'] = __('Your cart has been updated.') ?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
  <pre><?php print_r($json) ?></pre>
<?php else: ?>
  <?php echo json_encode($json) ?>
<?php endif ?>

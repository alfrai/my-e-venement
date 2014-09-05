<?php $json = $sf_data->getRaw('json') ?>
<?php $json['error']['message'] = __('An error occurred when updating your cart, try again please.') ?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
  <pre><?php print_r($json) ?></pre>
<?php else: ?>
  <?php echo json_encode($json) ?>
<?php endif ?>

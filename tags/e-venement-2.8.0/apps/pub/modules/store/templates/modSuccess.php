<?php $json = $sf_data->getRaw('json') ?>
<?php if (!( isset($json['success']['message']) && $json['success']['message'] )): ?>
<?php $json['success']['message'] = 'Your cart has been updated.' ?>
<?php endif ?>
<?php $json['success']['message'] = __($json['success']['message']) ?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
  <pre><?php print_r($json) ?></pre>
<?php else: ?>
  <?php echo json_encode($json) ?>
<?php endif ?>

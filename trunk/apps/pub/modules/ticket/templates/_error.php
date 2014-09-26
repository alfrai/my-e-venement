<?php $json = $sf_data->getRaw('json') ?>
<?php
if ( is_array($json['error']['message']) )
{
  foreach ( $json['error']['message'] as $key => $value )
    $json['error']['message'][$key] = __($value);
  $json['error']['message'] = implode(' ', $json['error']['message']);
}
?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
<pre><?php print_r($json) ?></pre>
<?php else: ?>
<?php echo json_encode($json) ?>
<?php endif ?>

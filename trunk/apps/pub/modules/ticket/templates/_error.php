<?php $json = $sf_data->getRaw('json') ?>
<?php
if ( is_array($json['error']['message']) )
{
  foreach ( $json['error']['message'] as $key => $value )
    $json['error']['message'][$key] = __($value);
  $json['error']['message'] = implode(' ', $json['error']['message']);
}
?>
<?php echo json_encode($json) ?>


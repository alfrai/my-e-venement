<?php
  $json = $sf_data->getRaw('json');
  $json['success']['data'] = $data;
  if ( isset($message) )
    $json['success']['message'] = $message;
  if ( isset($json['error']['message']) )
    $json['success']['message'] = false;
  include_partial('success', array('json' => $json));
?>

<?php
  $json = array('success' => array('data' => $data));
  if ( isset($message) )
    $json['success']['message'] = $message;
  include_partial('success', array('json' => $json));
?>

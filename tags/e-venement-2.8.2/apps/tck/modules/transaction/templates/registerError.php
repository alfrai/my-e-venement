<?php
  $json = array('error' => array(
    'message' => __('Please fill in the form correctly before you submit it'),
  ));
  
  echo json_encode($json);

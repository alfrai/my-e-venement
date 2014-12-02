<?php
  $vars = array(
    'options',
    'delimiter',
    'enclosure',
    'outstream',
    'charset',
    'lines',
    'prices',
  );
  foreach ( $vars as $key => $value )
  {
    $vars[$value] = $$value;
    unset($vars[$key]);
  }
  include_partial('csv',$vars);

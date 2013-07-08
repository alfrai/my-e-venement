<?php
 
include(dirname(__FILE__).'/../bootstrap/unit.php');
 
$t = new lime_test(10, new lime_output_color());

$bc = new liBarcode();

foreach ( array('0000000086356','0000012085354','0855012010307','0000000086479','000000009713') as $str )
{
  try {
    $decode = $bc->decode_ean($str);
  }
  catch(sfException $e)
  {
    $decode = false;
  }
  $t->isnt($decode,false,'checksum error: '.$str);
  $t->is($decode,intval(substr($str,0,strlen($str)-1)),'ean error: '.$str);
}

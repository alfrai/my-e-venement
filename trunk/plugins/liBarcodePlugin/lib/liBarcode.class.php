<?php
require_once ('Image/qrlib.php');
require_once ('php-barcode/php-barcode.php');

class liBarcode
{
  private $text = '';
  
  public function __construct($text)
  {
    $this->setText($text);
  }
  public function setText($text)
  {
    $this->text = $text;
  }
  
  public function render($file = NULL)
  {
    barcode_print('123456789012',false,1);
    return;
    /*
    return barcode_print($this->text,false,false,false);
    */
    return QRcode::png($this->text,$file);
  }
}

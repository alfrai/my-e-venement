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
    //return barcode_print($this->text,'ANY');
    return QRcode::png($this->text,$file);
  }
}

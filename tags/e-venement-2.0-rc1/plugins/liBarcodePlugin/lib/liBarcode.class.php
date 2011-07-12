<?php
require_once ('Image/qrlib.php');

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
    return QRcode::png($this->text,$file);
  }
}

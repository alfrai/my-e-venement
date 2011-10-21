<?php
require_once ('qrcode/qrlib.php');
require_once ('barcode/Barcode.php');

class liBarcode
{
  private $text = '';
  private $type = 'qrcode';
  
  public function __construct($text)
  {
    $this->setText($text);
    $this->type = sfConfig::get('app_tickets_barcode') != 'qrcode' ? 'barcode' : 'qrcode';
  }
  public function setText($text)
  {
    $this->text = $text;
  }
  
  public function render($file = NULL)
  {
    if ( $this->type == 'qrcode' )
      return QRcode::png($this->text,$file);
    else
    {
      $bc = new Image_Barcode_Code39('',2,4);
      $bc->draw($this->text,'jpg',true,120);
    }
  }
  
  public static function decode_ean($ean)
  {
    if ( strlen($ean) != 13 )
      throw new sfException('EAN barcode must have 13 digits');
    
    $str = str_split(substr($ean,0,12));
    $checksum = substr($ean,12,13);
    $sum = 0;
    foreach ( $str as $key => $value )
      $sum += $value*($key%2 == 0 ? 1 : 3);
    
    $v = ceil($sum/10)*10-$sum;
    
    if ( $v != $checksum )
      throw new sfException('EAN barcode given does not match ('.$v.' vs '.$checksum.')');
    
    return intval(substr($ean,0,12));
  }
}

<?php
require_once (__DIR__.'/qrcode/qrlib.php');

class liBarcode
{
  private $text = '';
  private $type = 'qrcode';
  
  public function __construct($text)
  {
    $this->setText($text);
    $this->type = sfConfig::get('app_tickets_barcode', 'qrcode') != 'qrcode' ? 'barcode' : 'qrcode';
  }
  public function setText($text)
  {
    $this->text = $text;
  }
  
  public function render($file = NULL)
  {
    return QRcode::png($this->text,$file, QR_ECLEVEL_M, 96, 0);
  }
  
  public function __toString()
  {
    $file = sfConfig::get('sf_app_cache_dir').'/ticket-'.rand(100000,999999).'.png';
    $this->render($file);
    $r = file_get_contents($file);
    unlink($file);
    return $r;
  }
  
  public static function decode_ean($ean)
  {
    if ( strlen($ean) == 12 ) $ean = '0'.$ean;
    if ( strlen($ean) != 13 )
      throw new sfException('EAN barcode must have 13 or 12 digits');
    
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

<?php

/**
 * PluginPicture
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginPicture extends BasePicture
{
  protected function resize()
  {
    if ( $this->width || $this->height )
    try {
      $resizer = new Imagick;
      if ( !$resizer->readImageBlob($this->getDecodedContent()) )
        throw new liEvenementException('A problem occurred during Image reading');
      
      $dest_scale = $this->width / $this->height;
      $orig_scale = $resizer->getImageWidth() / $resizer->getImageHeight();
      echo "dest: $dest_scale, orig: $orig_scale\n\n";
      if ( $dest_scale < $orig_scale )
        $resizer->resizeImage($this->width, $resizer->getImageHeight()*$this->width/$resizer->getImageWidth(), Imagick::FILTER_LANCZOS, 1);
      else
        $resizer->resizeImage($resizer->getImageWidth()*$this->height/$resizer->getImageHeight(), $this->height, Imagick::FILTER_LANCZOS, 1);
      
      $this->content = base64_encode($resizer->getImageBlob());
    }
    catch ( Exception $e ) {}
  }
  public function preSave($event)
  {
    $this->resize();
    parent::preSave($event);
  }
}

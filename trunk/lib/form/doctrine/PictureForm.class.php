<?php

/**
 * Picture form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PictureForm extends BasePictureForm
{
  // a hack for blob content which was erased on form initialization
  public function __construct(Picture $object = NULL)
  {
    if (!( $object instanceof Picture ))
      return parent::__construct();
    
    $buf = $object->content;
    $r = parent::__construct($object);
    if ( $object->content !== $buf )
      $object->content = $buf;
    
    return $r;
  }
  public function configure()
  {
    unset($this->widgetSchema['content'],$this->validatorSchema['content']);
    $this->widgetSchema   ['content_file'] = new sfWidgetFormInputFile();
    $this->validatorSchema['content_file'] = new sfValidatorFile(array(
      'mime_types' => array('image/gif', 'image/jpg', 'image/png', 'image/jpeg', 'application/x-gzip', 'image/svg+xml'),
    ));
    $this->validatorSchema['type']->setOption('required',false);
    $this->validatorSchema['name']->setOption('required',false);
  }
  
  public function doSave($con = NULL)
  {
    $this->translateValues();
    return parent::doSave($con);
  }
  
  // transforming the sfValidatedFile into Picture's properties
  public function translateValues()
  {
    $this->values['content']  = base64_encode(file_get_contents($this->values['content_file']->getTempName()));
    $this->values['name']     = $this->values['content_file']->getOriginalName();
    
    // mime type & encoding
    $type = PictureForm::getRealType($file);
    $this->values['type']     = $type['mime'];
    if ( isset($type['content-encoding']) )
      $this->values['content_encoding'] = $type['content-encoding'];
    
    unset($this->values['content_file']);
  }
  
  /**
    * @param  $file         sfValidatedFile the file for which we want the mime-type
    * @return array         an array componed by the "mime" type and the "content-encoding" to serve the file with, if needed or asked
    *
    **/
  public static function getRealType(sfValidatedFile $file)
  {
    $type = array('mime' => '', 'content-encoding' => NULL);
    // mime type
    if ( in_array($file->getOriginalExtension(), array('.svg', '.svgz')) && $file->getType() == 'application/x-gzip' )
    {
      // gzipped svg
      $type['mime']             = 'image/svg+xml';
      $type['content-encoding'] = 'gzip';
    }
    else
      $type['mime'] = $file->getType();
    
    return $type;
  }
}

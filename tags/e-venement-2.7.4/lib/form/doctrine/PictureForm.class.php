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
      'mime_types' => array('image/gif', 'image/jpg', 'image/png', 'image/jpeg'),
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
    $this->values['type']     = $this->values['content_file']->getType();
    unset($this->values['content_file']);
  }
}

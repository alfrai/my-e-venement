<?php

/**
 * Group form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GroupForm extends BaseGroupForm
{
  public function doSave($con = NULL)
  {
    $file = $this->values['picture']['content_file'];
    if ( $file instanceof sfValidatedFile )
    {
      // data translation
      $this->values['picture']['content']  = base64_encode(file_get_contents($file->getTempName()));
      $this->values['picture']['name']     = $file->getOriginalName();
      $this->values['picture']['type']     = $file->getType();
      $this->values['picture']['width']    = 24;
      $this->values['picture']['height']   = 16;
      unset($this->values['picture']['content_file']);
      
      // removing old picture to avoid useless storage
      if ( !$this->object->Picture->isNew() )
        $this->object->Picture->delete();
      
      // giving values to the picture (hack)
      foreach ( $this->values['picture'] as $field => $value )
        $this->object->Picture->$field = $value;
      $this->object->Picture->save($con);
      
      // associating the newly created picture to the current object (hack)
      $this->object->picture_id = $this->object->Picture->id;
    }
    else
    {
      unset($this->values['picture']);
    }
    
    return parent::doSave($con);
  }
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    
    $this->widgetSchema['contacts_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Contact',
      'url'   => url_for('contact/ajax'),
      'order_by' => array('name,firstname',''),
    ));
    
    $this->widgetSchema['professionals_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Professional',
      'url'   => url_for('professional/ajax'),
      'method'=> 'getFullName',
      'order_by' => array('c.name,c.firstname,o.name,t.name,p.name',''),
    ));
    $this->widgetSchema['professionals_list']->getJavascripts();
    $this->widgetSchema['professionals_list']->getStylesheets();
    
    $this->widgetSchema['organisms_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
      'order_by' => array('name,postalcode,city',''),
    ));
    $this->widgetSchema['organisms_list']->getJavascripts();
    $this->widgetSchema['organisms_list']->getStylesheets();
    
    // the group's owner
    $sf_user = sfContext::getInstance()->getUser();
    $this->validatorSchema['sf_guard_user_id'] = new sfValidatorInteger(array(
      'min' => $sf_user->getId(),
      'max' => $sf_user->getId(),
      'required' => true,
    ));
    $choices = array();
    if ( $sf_user->hasCredential('pr-group-common') )
    {
      $this->validatorSchema['sf_guard_user_id']->setOption('required',false);
      $choices[''] = '';
    }
    $choices[$sf_user->getId()] = $sf_user;
    $this->widgetSchema   ['sf_guard_user_id'] = new sfWidgetFormChoice(array(
      'choices'   => $choices,
      'default'   => $this->isNew() ? $sf_user->getId() : $this->getObject()->sf_guard_user_id,
    ));
    
    unset($this->widgetSchema['picture_id'], $this->validatorSchema['picture_id']);
  }
  
  public function setup()
  {
    $r = parent::setup();
    
    // pictures & co
    $picform = new PictureForm($this->object->Picture);
    $ws = $picform->getWidgetSchema();
    $vs = $picform->getValidatorSchema();
    
    unset($ws['width'], $ws['height'], $ws['type'], $ws['version'], $ws['name']);
    $vs['content_file']->setOption('required',false);
    
    $this->embedForm('picture', $picform);
    
    return $r;
  }
}

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
    $picform_name = 'Picture';
    $file = $this->values[$picform_name]['content_file'];
    unset($this->values[$picform_name]['content_file']);
    
    if (!( $file instanceof sfValidatedFile ))
      unset($this->embeddedForms[$picform_name]);
    else
    {
      // data translation
      $this->values[$picform_name]['content']  = base64_encode(file_get_contents($file->getTempName()));
      $this->values[$picform_name]['name']     = $file->getOriginalName();
      $this->values[$picform_name]['type']     = $file->getType();
      $this->values[$picform_name]['width']    = 24;
      $this->values[$picform_name]['height']   = 16;
    }
    
    return parent::doSave($con);
  }
  
  /*
  public function saveEmbeddedForms($con = null, $forms = null)
  {
    print_r($forms);
    die();
    if ( $forms !== null )
      return parent::saveEmbeddedForms($con, $forms);
    
    parent::saveEmbeddedForms($con, $forms);
  }
  */
  
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
    
    // pictures & co
    $this->object->Picture->Groups[] = $this->object;
    $this->embedRelation('Picture');
    foreach ( array('name', 'type', 'version', 'height', 'width',) as $fieldName )
      unset($this->widgetSchema['Picture'][$fieldName], $this->validatorSchema['Picture'][$fieldName]);
    $this->validatorSchema['Picture']['content_file']->setOption('required',false);
    unset($this->widgetSchema['picture_id'], $this->validatorSchema['picture_id']);
  }
}

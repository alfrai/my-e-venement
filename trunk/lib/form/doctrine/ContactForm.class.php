<?php

/**
 * Contact form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactForm extends BaseContactForm
{
  /**
   * @see AddressableForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Asset'));
    use_javascript('/sfFormExtraPlugin/js/double_list.js');
    
    //$this->widgetSchema   ['YOBs_list'] = new sfWidgetFormInputText(array('default' => $this->object->getYOBsString()));
    //$this->validatorSchema['YOBs_list'] = new sfValidatorString(array('required' => false));
    $this->object->orderYOBs()->YOBs[] = new YOB;
    $this->embedRelation('YOBs');
    
    if ( !$this->object->isNew() )
      $this->object->Relationships[] = new ContactRelationship;
    $this->embedRelation('Relationships');
    foreach ( $this->validatorSchema['Relationships']->getFields() as $arr )
    foreach ( array('from_contact_id', 'to_contact_id', 'contact_relationship_type_id') as $key )
      $arr[$key]->setOption('required', false);
    unset($this->widgetSchema['relations_list']);
    
    if ( sfConfig::get('app_contact_force_title', false) )
    $this->widgetSchema   ['title']     = new sfWidgetFormDoctrineChoice(array(
      'model' => 'TitleType',
      'add_empty' => true,
      'key_method' => '__toString',
    ));
    else
    $this->widgetSchema   ['title']     = new liWidgetFormDoctrineJQueryAutocompleterGuide(array(
      'model' => 'TitleType',
      'url'   => url_for('title_type/ajax'),
      'method_for_query' => 'findOneByName',
    ));
    
    $q = Doctrine::getTable('Group')->createQuery('g');
    $this->widgetSchema   ['groups_list']
      ->setOption('order_by', array('u.id IS NULL DESC, u.username, name',''))
      ->setOption('query', $q);
    $this->validatorSchema['groups_list']
      ->setOption('query', $q);
    
    $this->widgetSchema   ['phone_number'] = new sfWidgetFormInputText();
    $this->validatorSchema['phone_number'] = new sfValidatorString(array('required' => false));
    
    $this->widgetSchema   ['phone_type']   = new liWidgetFormDoctrineJQueryAutocompleterGuide(array(
      'model' => 'PhoneType',
      'url'   => url_for('phone_type/ajax'),
      'method_for_query' => 'findOneByName',
    ));
    $this->widgetSchema   ['phone_type']->getStylesheets();
    $this->widgetSchema   ['phone_type']->getJavascripts();
    $this->validatorSchema['phone_type'] = new sfValidatorPass(array(
      'required' => false,
    ));
    
    $this->validatorSchema['email'] = new liValidatorEmail(array(
      'required' => false,
    ));
    
    $this->widgetSchema['culture'] = new sfWidgetFormChoice(array(
      'choices' => sfConfig::get('project_internals_cultures', array('fr' => 'Français')),
    ));
    $this->validatorSchema['culture'] = new sfValidatorChoice(array(
      'choices' => array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français'))),
      'required' => false,
    ));
    
    $this->widgetSchema   ['sf_guard_user_id'] =
    $this->widgetSchema   ['confirmed'] = new sfWidgetFormInputHidden;
    
    $this->widgetSchema['type_of_resources_id']->setOption('order_by',array('name',''));
    $this->widgetSchema['familial_situation_id']->setOption('order_by',array('name',''));
    $this->widgetSchema['familial_quotient_id']->setOption('order_by',array('name',''));
    
    $this->widgetSchema['organism_category_id']->setOption('order_by',array('name',''));
    
    // adding artificial mandatory fields
    if ( is_array($force = sfConfig::get('app_contact_force_fields', array())) )
    foreach ( $force as $field )
    {
      if ( isset($this->validatorSchema[$field]) )
        $this->validatorSchema[$field]->setOption('required', true);
    }
    
    parent::configure();
  }
  
  public function isValid()
  {
    $v = parent::isValid();
    $su = !sfContext::hasInstance()
       || sfContext::hasInstance() && sfContext::getInstance()->getUser()->hasCredential('pr-admin');
    if ( !sfConfig::get('app_contact_force_one_phonenumber',false) || $su )
      return $v;
    
    if ( !(isset($this->values['phone_number']) && $this->values['phone_number']) && $this->object->Phonenumbers->count() == 0 )
    {
      $this->errorSchema->addError(new sfValidatorError(
        $this->validatorSchema['phone_number']->setMessage('required','required'),
        'You need to add at least one phonenumber',
        array()
      ), 'phone_number');
      return false;
    }
    
    return $v;
  }
  
  protected function doSave($con = NULL)
  {
    if ( isset($this->widgetSchema['Relationships']) )
    foreach ( $this->values['Relationships'] as $key => $values )
    if (!( isset($values['to_contact_id']) && $values['to_contact_id'] )
      ||!( isset($values['contact_relationship_type_id']) && $values['contact_relationship_type_id'] ))
    {
      unset(
        $this->object->Relationships[$key],
        $this->embeddedForms['Relationships']->embeddedForms[$key],
        $this->values['Relationships'][$key]
      );
    }
    else
      $this->object->Relationships[$key]->Contact = NULL; // hack ... to avoid an Exception based on a not-correct ->Contact
    
    if ( isset($this->widgetSchema['YOBs']) )
    foreach ( $this->values['YOBs'] as $key => $values )
    if (!( isset($values['year']) && trim($values['year']) ) && !( isset($values['name']) && trim($values['name']) ))
    {
      unset(
        $this->object->YOBs[$key],
        $this->embeddedForms['YOBs']->embeddedForms[$key],
        $this->values['YOBs'][$key]
      );
    }
    
    // force uppercase
    if ( is_array($upper = sfConfig::get('app_contact_force_uppercase', array())) )
    foreach ( $upper as $field )
    if ( isset($this->values[$field]) )
      $this->values[$field] = strtoupper($this->values[$field]);
    
    // force uppercase first letter
    if ( is_array($upper = sfConfig::get('app_contact_force_ucfirst', array())) )
    foreach ( $upper as $field )
    if ( isset($this->values[$field]) )
      $this->values[$field] = ucfirst($this->values[$field]);
    
    return parent::doSave($con);
  }
  
  public function save($con = null)
  {
    $r = parent::save($con);
    
    // this is useless with the TDP design:
    if ( isset($this->widgetSchema['YOBs_list']) )
    {
      // get back given values
      $given = explode(',',str_replace(' ','',$this->getValue('YOBs_list')));
      
      // get back existing records
      $indb = array();
      foreach ( $this->object->YOBs as $YOB )
        $indb[$YOB->id] = $YOB;
      
      // forget all values / records which are already recorded
      foreach ( $given as $key => $value )
      if ( ($id = array_search($value,$indb)) !== false )
      {
        unset($indb[$id]);
        unset($given[$key]);
      }
      
      // remove all existing records which have not been committed
      foreach ( $indb as $id => $YOB )
        $YOB->delete($con);
      
      // add all values committed which are not in DB
      foreach ( $given as $key => $value )
      if ( intval($value) )
      {
        $YOB = new YOB();
        $YOB->year = $value;
        $YOB->contact_id = $this->object->id;
        $YOB->save($con);
      }
    }
    
    return $r;
  }
  
  public function setStrict($strict = true)
  {
    foreach ( array('firstname','email') as $key )
      $this->validatorSchema[$key]->setOption('required',$strict);
  }
  
  public function displayOnly($fieldname = NULL)
  {
    unset(
      $this->widgetSchema['emails_list'],
      $this->widgetSchema['groups_list'],
      $this->widgetSchema['YOBs_list'],
      $this->widgetSchema['YOBs'],
      $this->widgetSchema['Relationships'],
      $this->widgetSchema['relations_list'],
      $this->widgetSchema['back_relations_list']
    );
    
    // BUG: 2013-04-12
    if ( is_null($fieldname) )
      return $this;
    
    if ( !($this->widgetSchema[$fieldname] instanceof sfWidgetForm) )
      throw new liEvenementException('Fieldname "'.$fieldname.'" not found.');
    
    foreach ( $this->widgetSchema->getFields() as $name => $widget )
    {
      if ( $name != $fieldname )
        $this->widgetSchema[$name] = new sfWidgetFormInputHidden();
    }
    
    return $this;
  }
  
  public function saveGroupsList($con = null)
  {
    $this->correctGroupsListWithCredentials();
    return parent::saveGroupsList($con);
  }
  public function saveEmailsList($con = null)
  {
    // BUG: 2013-04-12
    return;
  }
}

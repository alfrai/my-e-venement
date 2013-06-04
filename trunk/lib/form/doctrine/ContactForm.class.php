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

    $this->object->Relationships[] = new ContactRelationship;
    $this->embedRelation('Relationships');
    foreach ( $this->validatorSchema['Relationships']->getFields() as $arr )
    foreach ( array('to_contact_id', 'contact_relationship_type_id') as $key )
      $arr[$key]->setOption('required', false);
    unset($this->widgetSchema['relations_list']);
    
    $this->widgetSchema   ['title']     = new liWidgetFormDoctrineJQueryAutocompleterGuide(array(
      'model' => 'TitleType',
      'url'   => url_for('title_type/ajax'),
      'method_for_query' => 'findOneByName',
    ));
    
    $q = Doctrine::getTable('Group')->createQuery('g');
    if ( sfContext::hasInstance() )
    {
      $q->where('(TRUE')
        ->andWhere('g.sf_guard_user_id = ?',sfContext::getInstance()->getUser()->getId());
      if ( sfContext::getInstance()->getUser()->hasCredential('pr-group-common') )
        $q->orWhere('g.sf_guard_user_id IS NULL');
      $q->andWhere('TRUE)');
    }
    $this->widgetSchema   ['groups_list']
      ->setOption('order_by', array('u.id IS NULL DESC, u.username, name',''))
      ->setOption('query', $q);
    $this->validatorSchema['groups_list']
      ->setOption('query', $q);
    
    $this->widgetSchema   ['phone_number'] = new sfWidgetFormInputText();
    $this->validatorSchema['phone_number'] = new sfValidatorPass(array('required' => false));
    
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
    
    $this->widgetSchema   ['confirmed'] = new sfWidgetFormInputHidden();
    
    parent::configure();
  }
  
  protected function doSave($con = NULL)
  {
    foreach ( $this->values['YOBs'] as $key => $values )
    if (!( isset($values['year']) && trim($values['year']) ))
    {
      unset(
        $this->object->YOBs[$key],
        $this->embeddedForms['YOBs']->embeddedForms[$key],
        $this->values['YOBs'][$key]
      );
    }
    
    $r = parent::doSave($con);
    return $r;
  }
  public function save($con = null)
  {
    $r = parent::save($con);
    
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
      $this->widgetSchema['Relationships'],
      $this->widgetSchema['relations_list']
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

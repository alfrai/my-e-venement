<?php

/**
 * Contact form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactPublicForm extends ContactForm
{
  public function configure()
  {
    parent::configure();
    
    foreach ( array(
        'YOBs_list', 'groups_list', 'emails_list', 'family_contact',
        'organism_category_id', 'description', 'password', 'npai',
        'latitude', 'longitude', 'slug', 'confirmed',
        'familial_quotient_id', 'type_of_resources_id', 'familial_situation_id') as $field )
      unset($this->widgetSchema[$field], $this->validatorSchema[$field]);
    
    $this->widgetSchema['title'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'TitleType',
      'add_empty' => true,
      'key_method' => 'getName',
    ));
    $this->widgetSchema['phone_type'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'PhoneType',
      'key_method' => '__toString',
      'add_empty' => true,
    ));
    
    $this->widgetSchema   ['password']        = new sfWidgetFormInputPassword();
    $this->widgetSchema   ['password_again']  = new sfWidgetFormInputPassword();
    $this->validatorSchema['password']        = new sfValidatorString(array('required' => true, 'min_length' => 4));
    $this->validatorSchema['password_again']  = new sfValidatorString(array('required' => false));
    
    foreach ( array('firstname','address','postalcode','city','email') as $field )
      $this->validatorSchema[$field]->setOption('required', true);
    
    $this->widgetSchema->setPositions($arr = array(
      'id',
      'title','name','firstname',
      'address','postalcode','city','country',
      'email','phone_type','phone_number',
      'password','password_again',
    ));
    
    $this->validatorSchema['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'query' => Doctrine_Query::create()->from('Contact c'),
      'required' => false,
    ));
  }
  
  public function bind(array $taintedValues = NULL, array $taintedFiles = NULL)
  {
    parent::bind($taintedValues);
    if ( $this->getValue('password') !== $this->getValue('password_again') )
      $this->errorSchema->addError(new sfValidatorError($this->validatorSchema['password_again'],'Passwords do not match.'));
  }
  
  public function isValid()
  {
    if ( !parent::isValid() )
      return false;
    
    if ( $this->object->isNew() )
    {
      $login = new LoginForm();
      $login->bind(array('email' => $this->getValue('email'), 'password' => $this->getValue('password')));
      if ( $login->isValid()
        && (!sfContext::getInstance()->getUser()->hasAttribute('contact_id') || sfContext::getInstance()->getUser()->getAttribute('contact_id') != $login->getContact()->id) )
        throw new liOnlineSaleException('A contact with the same values already exists, try to authenticate...');
    }
    
    return true;
  }
  
  public function save($con = NULL)
  {
    if ( is_null($this->object->confirmed) )
      $this->object->confirmed = false;
    
    if ( $this->getValue('phone_number') )
    {
      $new_number = true;
      foreach ( $this->object->Phonenumbers as $pn )
      if ( strcasecmp($pn->name,$this->getValue('phone_type')) == 0 )
      {
        $pn->number = $this->getValue('phone_number');
        $new_number = false;
        break;
      }
      
      if ( $new_number )
      {
        $pn = new ContactPhonenumber;
        $pn->name = $this->getValue('phone_type');
        $pn->number = $this->getValue('phone_number');
        
        $this->object->Phonenumbers[] = $pn;
      }
    }
    
    return parent::save($con);
  }
  
  public function removePassword()
  {
    unset(
      $this->widgetSchema   ['password'],
      $this->widgetSchema   ['password_again'],
      $this->validatorSchema['password'],
      $this->validatorSchema['password_again']
    );
  }
}

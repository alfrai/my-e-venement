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
    
    $this->disableCSRFProtection();
    
    foreach ( array(
        'sf_guard_user_id', 'back_relations_list', 'Relationships', 'YOBs',
        'YOBs_list', 'groups_list', 'emails_list', 'family_contact', 'relations_list',
        'organism_category_id', 'description', 'password', 'email_no_newsletter', 'npai', 'flash_on_control',
        'latitude', 'longitude', 'slug', 'confirmed', 'version', 'culture', 'picture_id',
        'shortname', 'involved_in_list',
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
    $this->widgetSchema   ['password']->setLabel('New password');
    
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
    
    // if the contact is a professional
    if ( sfConfig::get('app_contact_professional', false) )
    {
      unset($this->widgetSchema['phone_type'], $this->validatorSchema['phone_type']);
      
      unset($this->widgetSchema['phone_number'], $this->validatorSchema['phone_number']);
      $this->widgetSchema   ['pro_phone_number'] = new sfWidgetFormInput;
      $this->validatorSchema['pro_phone_number'] = new sfValidatorString(array('required' => false));
      $this->widgetSchema   ['pro_phone_number']->setLabel('Phone number')->setDefault($this->object->Professionals[0]->contact_number);
      
      unset($this->widgetSchema['email'], $this->validatorSchema['email']);
      $this->widgetSchema   ['pro_email'] = new sfWidgetFormInput;
      $this->validatorSchema['pro_email'] = new sfValidatorEmail;
      $this->widgetSchema   ['pro_email']->setLabel('Email')->setDefault($this->object->Professionals[0]->contact_email);
      
      $this->widgetSchema['pro_organism'] = new sfWidgetFormInput(array(), array('disabled' => 'disabled'));
      $this->widgetSchema['pro_organism']->setDefault($this->object->Professionals[0]->Organism)->setLabel('Organism');
      $this->widgetSchema['pro_address'] = new sfWidgetFormTextarea(array(), array('disabled' => 'disabled'));
      $this->widgetSchema['pro_address']->setDefault(
        trim($this->object->Professionals[0]->Organism->address)
        ."\n".
        $this->object->Professionals[0]->Organism->postalcode
        ." ".
        $this->object->Professionals[0]->Organism->city
        ."\n".
        $this->object->Professionals[0]->Organism->country
      )->setLabel('Address');
      
      foreach ( array('address', 'postalcode', 'city', 'country') as $field )
        unset($this->widgetSchema[$field], $this->validatorSchema[$field]);
      
      $this->widgetSchema->setPositions($arr = array(
        'id',
        'title','name','firstname',
        'pro_organism', 'pro_address',
        'pro_email','pro_phone_number',
        'password','password_again',
      ));
    
      if ( sfConfig::get('app_contact_modify_coordinates_first', false) )
      {
        $this->widgetSchema   ['comment'] = new sfWidgetFormTextarea;
        $this->widgetSchema   ['comment']->setLabel('Some changes to submit?');
        $this->widgetSchema   ['comment']->setDefault(sfContext::getInstance()->getUser()->getTransaction()->Professional->description);
        $this->validatorSchema['comment'] = new sfValidatorString;
        $this->validatorSchema['comment']->setOption('required', false);
      }
    }
    
    $vel = sfConfig::get('app_tickets_vel',array());
    if ( isset($vel['one_shot']) && $vel['one_shot'] )
      unset($this->widgetSchema['password'], $this->widgetSchema['password_again']);
    
    if ( sfContext::hasInstance() )
      sfContext::getInstance()->getUser()->addCredential('pr-group-common');
    $q = Doctrine::getTable('Group')->createQuery('g')->andWhere('g.sf_guard_user_id IS NULL');
    if ( sfContext::hasInstance() )
      sfContext::getInstance()->getUser()->removeCredential('pr-group-common');
    if ( $q->count() > 0 )
    {
      $this->validatorSchema['special_groups_list'] = new sfValidatorDoctrineChoice(($arr = array(
        'model' => 'Group',
        'query' => $q,
        'multiple' => true,
      )) + array('required' => false));
      $this->widgetSchema   ['special_groups_list'] = new sfWidgetFormDoctrineChoice($arr + array(
        'expanded' => true,
        'order_by' => array('g.name', ''),
      ));
      $this->widgetSchema   ['special_groups_list']->setLabel('Options');
      $this->setDefault('special_groups_list', sfConfig::get('app_contact_professional', false)
        ? $this->object->Professionals[0]->Groups->getPrimaryKeys()
        : $this->object->Groups->getPrimaryKeys()
      );
    }
  }
  
  public function bind(array $taintedValues = NULL, array $taintedFiles = NULL)
  {
    parent::bind($taintedValues, $taintedFiles);
    
    // add a validator to avoid duplicates
    if ( $this->object->isNew() )
    {
      $q = Doctrine_Query::create()
        ->from('Contact c');
      $this->validatorSchema['duplicate'] = new liValidatorContact(array(
        'query' => $q,
        'required' => true,
      ));
      $q = $this->validatorSchema['duplicate']->getOption('query');
      foreach ( array('name', 'firstname', 'email') as $field )
        $q->andWhere("c.$field ILIKE ?",$this->getValue($field));
    }
    
    if ( $this->getValue('password') !== $this->getValue('password_again') )
      $this->errorSchema->addError(new sfValidatorError($this->validatorSchema['password_again'],'Passwords do not match.'));
    
    // bind again for the new validators
    parent::bind($taintedValues, $taintedFiles);
  }
  
  public function save($con = NULL)
  {
    // formatting central data
    $this->object->name = trim($this->object->name);
    $this->object->firstname = trim($this->object->firstname);
    
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
    
    // if no password set
    if ( !$this->object->isNew() && !trim($this->getValue('password')) )
      $this->values['password'] = $this->object->password;
    
    // if the contact is a professional
    if ( sfConfig::get('app_contact_professional', false) )
    {
      foreach ( array('pro_email' => 'contact_email', 'pro_phone_number' => 'contact_number') as $vname => $field )
        $this->object->Professionals[0]->$field = $this->values[$vname];
      
      // the comment on coordinates
      if ( trim($this->getValue('comment')) && sfContext::hasInstance() )
      {
        $transaction = sfContext::getInstance()->getUser()->getTransaction();
        $transaction->Professional->description = $this->getValue('comment');
        $transaction->save();
      }
    }
    
    return parent::save($con);
  }
  
  public function saveGroupsList($con = NULL)
  {
    if (!$this->isValid())
      throw $this->getErrorSchema();
    
    // somebody has unset this widget
    if (!isset($this->widgetSchema['special_groups_list']))
      return;
    
    if (null === $con)
      $con = $this->getConnection();
    
    $object = sfConfig::get('app_contact_professional', false) ? $this->object->Professionals[0] : $this->object;
    
    $q = Doctrine_Query::create()->from('Group g')
      ->andWhere('g.sf_guard_user_id IS NULL')
      ->leftJoin('g.Users u')
      ->andWhere('u.id = ?', sfContext::getInstance()->getUser()->getId());
    $groups = $q->execute();
    
    $possible = $groups->getPrimaryKeys();
    $values = $this
      ->correctGroupsListWithCredentials('special_groups_list', $object)
      ->getValue('special_groups_list')
    ;
    
    if (!is_array($values))
      $values = array();
    
    $unlink = array_diff($possible, $values);
    if (count($unlink))
      $object->unlink('Groups', array_values($unlink));
    
    foreach ( $values as $gid )
    if ( in_array($gid, $possible) )
    {
      foreach ( $groups as $group )
      if ( $group->id == $gid )
      {
        $object->Groups[] = $group;
      }
    }
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

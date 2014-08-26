<?php

/**
 * Manifestation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationForm extends BaseManifestationForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['organizers_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    if ( $this->object->isNew() )
      $this->widgetSchema['participants_list'] = new sfWidgetFormInputHidden;
    else
    {
      $this->widgetSchema['participants_list']->setOption('query', Doctrine::getTable('Contact')->createQuery('c')
        ->leftJoin('o.Manifestations m')
        ->leftJoin('c.InvolvedIn ii')
        ->andWhere('(TRUE')
        ->andWhere('m.id = ?', $this->object->id)
        ->orWhere('ii.id = ?', $this->object->id)
        ->andWhere('TRUE)')
      )
      ->setOption('order_by', array('c.name, c.firstname'))
      ->setOption('expanded', true);
    }
    $this->validatorSchema['participants_list']->setOption('query', $this->widgetSchema['participants_list']->getOption('query'));
    
    $this->widgetSchema['workspaces_list']->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    $this->widgetSchema['event_id']
      ->setOption('query',EventFormFilter::addCredentialsQueryPart(Doctrine::getTable('Event')->createQuery()))
      ->setOption('order_by', array('translation.name', ''));
    $this->widgetSchema['color_id']
      ->setOption('order_by',array('name',''))
      ->setOption('method', 'getName');
    
    $this->widgetSchema['location_id']
      ->setOption('add_empty',true)
      ->setOption('order_by',array('rank, name',''))
      ->setOption('query', $q = Doctrine::getTable('Location')->retrievePlaces());
    $this->validatorSchema['location_id']->setOption('query', $q);
    
    // duration stuff
    $this->widgetSchema['ends_at'] = new liWidgetFormDateTime(array(
      'date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'time' => new liWidgetFormTimeText(),
    ));
    $this->validatorSchema['ends_at'] = new sfValidatorDateTime(array('required' => false));
    $this->validatorSchema['duration'] = new sfValidatorString(array('required' => false));
    
    $this->widgetSchema['vat_id']
      ->setOption('order_by', array('value, name', ''));
    $this->widgetSchema['depends_on'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url'   => url_for('manifestation/ajax?except='.$this->object->id),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',10).' }',
    ));
    
    // reservation
    $config = sfConfig::get('app_manifestation_reservations',array('enable' => false));
    
    // misc permissions for single fields (but particularly on reservation stuff)
    if ( sfContext::hasInstance() )
    {
      $credentials = Manifestation::getCredentials();
      $sf_user = sfContext::getInstance()->getUser();
      
      if ( !$sf_user->hasCredential($credentials['contact_id']) )
        $this->widgetSchema['contact_id'] = new sfWidgetFormInputHidden;
      
      if ( !$sf_user->hasCredential($credentials['reservation_confirmed']) && !(
           isset($config['let_restricted_users_confirm'])
        && $config['let_restricted_users_confirm']
        && $sf_user->getContactId() === $this->object->contact_id
      ))
        $this->widgetSchema['reservation_confirmed'] = new sfWidgetFormInputHidden;
    }
    
    // removing required options from fields that should be filled automatically in the Manifestation objet
    foreach ( array('reservation_begins_at', 'reservation_ends_at',) as $fieldName )
      $this->validatorSchema[$fieldName]->setOption('required', false);
    $this->widgetSchema['booking_list']->setOption('expanded', true)
      ->setOption('order_by', array('place, rank IS NULL, rank, name',''));
    if (!( isset($config['enable']) && $config['enable'] ))
    foreach ( array('contact_id', 'reservation_begins_at', 'reservation_ends_at', 'blocking', 'reservation_confirmed', 'reservation_optional', 'reservation_description') as $fieldName )
      $this->widgetSchema[$fieldName] = new sfWidgetFormInputHidden;
    
    // extra informations
    if ( !$this->object->isNew() && sfConfig::get('app_manifestation_extra_informations_enable',true) )
    {
      for ( $i = 0 ; $i < 3 ; $i++ )
        $this->object->ExtraInformations[] = new ManifestationExtraInformation;
      $this->embedRelation('ExtraInformations');
      for ( $i = 0 ; $i < $this->object->ExtraInformations->count() ; $i++ )
      {
        $this->validatorSchema['ExtraInformations'][$i]['name']->setOption('required', false);
        $this->validatorSchema['ExtraInformations'][$i]['value']->setOption('required', false);
      }
    }
    
    // default values from config file
    foreach ( sfConfig::get('app_manifestation_defaults',array()) as $fieldName => $default )
      $this->setDefault($fieldName, $default);
    
    // default contact
    if ( sfContext::hasInstance() && $this->object->isNew() )
    if ( $contact = sfContext::getInstance()->getUser()->getContact() )
      $this->setDefault('contact_id', $contact->id);
    
    parent::configure();
  }
  
  public function save($con = NULL)
  {
    $event = NULL;
    
    if ( $this->values['vat_id'] == '' || is_null($this->values['vat_id']) )
    {
      $event = Doctrine::getTable('Event')->findOneById($this->values['event_id']);
      $this->values['vat_id'] = $event->EventCategory->vat_id;
    }
    if ( $this->values['duration'] === '' || is_null($this->values['duration']) )
    {
      $event = $event instanceof Event
        ? $event
        : Doctrine::getTable('Event')->findOneById($this->values['event_id']);
      $this->values['duration'] = $event->duration;
    }
    
    return parent::save($con);
  }
  
  protected function doSave($con = null)
  {
    $this->saveOrganizersList($con);
    $this->saveBookingList($con);
    if ( $this->isNew() )
      $this->saveWorkspacesList($con);
    else
      $this->saveParticipantsList($con);
    
    foreach ( $this->values['ExtraInformations'] as $key => $ei )
    if ( !(isset($ei['name']) && $ei['name']) )
    {
      unset(
        $this->embeddedForms['ExtraInformations'][$key],
        $this->values['ExtraInformations'][$key],
        $this->object->ExtraInformations[$key]
      );
    }
    
    BaseFormDoctrine::doSave($con);
  }
}

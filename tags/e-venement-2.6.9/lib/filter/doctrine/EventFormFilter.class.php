<?php

/**
 * Event filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventFormFilter extends BaseEventFormFilter
{
  public function configure()
  {
    $this->widgetSchema['companies_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => 'organism/ajax',
    ));
    
    $this->widgetSchema   ['meta_event_id']->setOption('multiple',true);
    $this->widgetSchema   ['meta_event_id']->setOption('query', Doctrine::getTable('MetaEvent')->createQuery('me')
      ->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials()))
    );
    $this->widgetSchema   ['meta_event_id']->setOption('add_empty',false);
    $this->widgetSchema   ['meta_event_id']->setOption('order_by',array('name',''));
    $this->validatorSchema['meta_event_id']->setOption('multiple',true);
    
    $this->widgetSchema['event_category_id']->setOption('order_by',array('name',''));
    
    $this->widgetSchema   ['resource_id'] = new sfWidgetFormDoctrineChoice(array(
      'add_empty' => true,
      'model'     => 'Location',
      'order_by'  => array('place DESC, name',''),
    ));
    $this->validatorSchema['resource_id'] = new sfValidatorDoctrineChoice(array(
      'required'  => false,
      'model'     => 'Location',
    ));
    
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema   ['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model'     => 'Contact',
      'url'       => cross_app_url_for('rp', 'contact/ajax'),
    ));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array(
      'required'  => false,
      'model'     => 'Contact',
    ));
    
    $this->widgetSchema   ['manif_confirmed'] =
    $this->widgetSchema   ['manif_optional'] =
    $this->widgetSchema   ['manif_conflict'] =
    $this->widgetSchema   ['manif_blocking'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('' => "doesn't matter", 1 => 'only them', 0 => 'exclude them'),
    ));
    $this->validatorSchema['manif_confirmed'] =
    $this->validatorSchema['manif_optional'] =
    $this->validatorSchema['manif_conflict'] =
    $this->validatorSchema['manif_blocking'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
  }
  public function buildQuery(array $values)
  {
    return $this->addCredentialsQueryPart(
      parent::buildQuery($values)
    );
  }
  
  /*
  public function getFields()
  {
    return array_merge(parent::getFields(),array(
      'manif_confirmed' => 'TranslatedBoolean',
      'manif_blocking'  => 'TranslatedBoolean',
    ));
  }
  */
  protected function getTranslatedFields($fieldName = NULL)
  {
    $fields = array(
      'manif_optional'  => 'm.reservation_optional',
      'manif_confirmed' => 'm.reservation_confirmed',
      'manif_blocking'  => 'm.blocking',
    );
    
    return !is_null($fieldName)
      ? $fields[$fieldName]
      : $fields;
  }
  
  public static function addCredentialsQueryPart(Doctrine_Query $query, $me = 'me')
  {
    return $query
      ->andWhere('(TRUE')
      ->andWhere("$me.id IS NULL")
      ->orWhereIn("$me.id",array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials()))
      ->andWhere('TRUE)');
  }
  
  public function addResourceIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( !$q->contains("LEFT JOIN $a.Manifestations m") )
      $q->leftJoin("$a.Manifestations m");
    
    if ( intval($value) > 0 )
      return $q
        ->leftJoin('m.Booking b')
        ->andWhere('(TRUE')
        ->andWhere('m.location_id = ?', intval($value))
        ->orWhere('b.id = ?', intval($value))
        ->andWhere('TRUE)');
    return $q;
  }
  public function addContactIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( !$q->contains("LEFT JOIN $a.Manifestations m") )
      $q->leftJoin("$a.Manifestations m");
    
    if ( intval($value) > 0 )
      return $q->andWhere('m.contact_id = ?',intval($value));
    return $q;
  }
  public function addManifConflictColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !in_array($value,array(0,1)) )
      return $q;
    
    $a = $q->getRootAlias();
    if ( !$q->contains("LEFT JOIN $a.Manifestations m") )
      $q->leftJoin("$a.Manifestations m");
    
    $conflicts = Doctrine::getTable('Manifestation')->getConflicts(array('potentially' => true));
    return $q->andWhereIn('m.id', array_keys($conflicts));
  }
  public function addManifOptionalColumnQuery(Doctrine_Query $q, $field, $value)
  { return $this->addTranslatedBooleanQuery($q, $field, $value); }
  public function addManifConfirmedColumnQuery(Doctrine_Query $q, $field, $value)
  { return $this->addTranslatedBooleanQuery($q, $field, $value); }
  public function addManifBlockingColumnQuery(Doctrine_Query $q, $field, $value)
  { return $this->addTranslatedBooleanQuery($q, $field, $value); }
  public function addTranslatedBooleanQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( !$q->contains("LEFT JOIN $a.Manifestations m") )
      $q->leftJoin("$a.Manifestations m");
    
    if ( !in_array($value,array(0,1)) )
      return $q;
    return $q->andWhere($this->getTranslatedFields($field).' = ?', $value == 1);
  }
  
  public function addAgeMinColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( isset($value['text']) && $value['text'] )
      $q->andWhere("$a.$field <= ?",$value['text']);
  }
  public function addAgeMaxColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( isset($value['text']) && $value['text'] )
      $q->andWhere("$a.$field >= ?",$value['text']);
  }
}

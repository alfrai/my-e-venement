<?php

/**
 * Order filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OrderFormFilter extends BaseOrderFormFilter
{
  /**
   * @see AccountingFormFilter
   */
  public function configure()
  {
    parent::configure();
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $this->widgetSchema['created_at'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormDateText(array('culture' => 'fr')),
      'to_date'   => new liWidgetFormDateText(array('culture' => 'fr')),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span>', null, 'sf_admin'),
    ));
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText();
    
    $this->widgetSchema   ['manifestation_happens_at'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormDateText(array('culture' => 'fr')),
      'to_date'   => new liWidgetFormDateText(array('culture' => 'fr')),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span>', null, 'sf_admin'),
    ));
    $this->validatorSchema['manifestation_happens_at'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false)),
      'to_date'   => new sfValidatorDate(array('required' => false)),
      'required'  => false,
    ));
    
    $this->widgetSchema   ['event_name'] = new sfWidgetFormInput;
    $this->validatorSchema['event_name'] = new sfValidatorString(array('required' => false));
    
    $this->widgetSchema   ['workspaces_list'] = new sfWidgetFormDoctrineChoice($arr = array(
      'model'     => 'Workspace',
      'query'     => Doctrine::getTable('Workspace')->createQuery('ws')->select('ws.*')->leftJoin('ws.Users u'),
      'order_by'  => array('name', ''),
      'multiple'  => true,
    ));
    unset($arr['order_by']);
    $this->validatorSchema['workspaces_list'] = new sfValidatorDoctrineChoice($arr + array('required' => false));
    $this->widgetSchema   ['meta_events_list'] = new sfWidgetFormDoctrineChoice($arr = array(
      'model'     => 'MetaEvent',
      'query'     => Doctrine::getTable('MetaEvent')->createQuery('me')->select('me.*')->leftJoin('me.Users u'),
      'order_by'  => array('name', ''),
      'multiple'  => true,
    ));
    unset($arr['order_by']);
    $this->validatorSchema['meta_events_list'] = new sfValidatorDoctrineChoice($arr + array('required' => false));
    if ( sfContext::hasInstance() )
    {
      $sf_user = sfContext::getInstance()->getUser();
      $this->widgetSchema   ['workspaces_list']->getOption('query')
        ->andWhere('u.id = ?', $sf_user->getId());
      $this->widgetSchema   ['meta_events_list']->getOption('query')
        ->andWhere('u.id = ?', $sf_user->getId());
    }
    
    $this->widgetSchema['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));
    $this->widgetSchema['organism_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Organism',
      'required' => false,
    ));
  }
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['event_name']               = 'EventName';
    $fields['contact_id']               = 'ContactId';
    $fields['organism_id']              = 'OrganismId';
    $fields['manifestation_happens_at'] = 'ManifestationHappensAt';
    $fields['meta_events_list']         = 'MetaEventsList';
    $fields['workspaces_list']          = 'WorkspacesList';
    return $fields;
  }
  
  public function addWorkspacesListColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    if ( !is_array($values) )
      $value = array($values);
    
    $q->andWhereIn('g.workspace_id', $values);
    return $q;
  }
  public function addMetaEventsListColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    if ( !is_array($values) )
      $value = array($values);
    
    $q->andWhereIn('me.id', $values);
    return $q;
  }
  public function addContactIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !trim($value) )
      return $q;
    
    $q->andWhere('c.id = ?', $value);
    
    return $q;
  }
  public function addOrganismIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !trim($value) )
      return $q;
    if ( !$q->contains('LEFT JOIN p.Organism o') )
      $q->leftJoin('p.Organism o');
    $q->andWhere('o.id = ?', $value);
    
    return $q;
  }
  
  public function addEventNameColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !trim($value) )
      return $q;
    
    $events = Doctrine::getTable('Event')->search($value.'*', Doctrine::getTable('Event')->createQuery('e'))
      ->select('e.id')
      ->limit(500)
      ->fetchArray();
    $eids = array();
    foreach ( $events as $event )
      $eids[] = $event['id'];
    $q->andWhereIn('e.id',$eids);
    
    return $q;
  }
  
  public function addManifestationHappensAtColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if (!( $value && is_array($value)
        && isset($value['to']) && isset($value['from'])
        && trim($value['from']) && trim($value['to']) ))
      return $q;
    
    $q->andWhere('m.happens_at >= ?', $value['from'])
      ->andWhere('m.happens_at <= ?', $value['to']);
    
    return $q;
  }
}

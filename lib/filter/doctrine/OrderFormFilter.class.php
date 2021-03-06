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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N', 'CrossAppLink'));
    
    $this->widgetSchema['created_at'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => 'fr')),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => 'fr')),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span>', null, 'sf_admin'),
    ));
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText();
    
    $this->widgetSchema   ['manifestation_happens_at'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => 'fr')),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => 'fr')),
      'template'  => '<span class="dates"><span>'.__('From %from_date%').'</span> <span>'.__('to %to_date% excluded').'</span>',
      'with_empty' => false,
    ));
    $this->validatorSchema['manifestation_happens_at'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false)),
      'to_date'   => new sfValidatorDate(array('required' => false)),
      'required'  => false,
    ));
    
    $this->widgetSchema   ['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event', 'manifestation/ajax'),
    ));
    $this->validatorSchema['manifestations_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'required' => false,
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['has_confirmed_ticket'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('' => __('yes or no',null,'sf_admin'), 'yes' => __('yes',null,'sf_admin'), 'no' => __('no',null,'sf_admin')),
    ));
    $this->validatorSchema['has_confirmed_ticket'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
    
    $this->widgetSchema   ['closed'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('na' => __('yes or no',null,'sf_admin'), 'yes' => __('yes',null,'sf_admin'), 'no' => __('no',null,'sf_admin')),
    ));
    $this->validatorSchema['closed'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
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
    $fields['has_confirmed_ticket']     = 'HasConfirmedTicket';
    $fields['closed']     = 'Closed';
    $fields['event_name']               = 'EventName';
    $fields['contact_id']               = 'ContactId';
    $fields['organism_id']              = 'OrganismId';
    $fields['manifestation_happens_at'] = 'ManifestationHappensAt';
    $fields['meta_events_list']         = 'MetaEventsList';
    $fields['workspaces_list']          = 'WorkspacesList';
    $fileds['manifestations_list']      = 'ManifestationsList';
    return $fields;
  }
  
  public function addClosedColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $value == 'na' )
      return $q;
    
    $o = $q->getRootAlias();
    $q->andWhere('t.closed = ?', $value == 'no' ? false : true);
    return $q;
  }
  public function addHasConfirmedTicketColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    
    $o = $q->getRootAlias();
    $ids = array(0);
    foreach ( $tmp = $q->copy()->select("$o.id, $o.transaction_id")->fetchArray() as $order )
      $ids[] = $order['transaction_id'];
    
    $tids = array(0);
    foreach ( $tmp = Doctrine_Query::create()->from('Transaction t')
      ->andWhereIn('t.id', $ids)
      ->leftJoin('t.Tickets tck')
      ->andWhere('(tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL)')
      ->select('t.id AS id')
      ->groupBy('t.id')
      ->having('count(tck.id) '.($values == 'yes' ? '> 0' : '= 0'))
      ->fetchArray() as $transaction )
      $tids[] = $transaction['id'];
    $q->andWhereIn("$o.transaction_id", $tids);
    return $q;
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

  public function addManifestationsListColumnQuery(Doctrine_Query $q, $field, $values)
  {
      if ( !$values )
        return $q;
      if ( !is_array($values) )
        $value = array($values);

    $q->andWhereIn('m.id', $values);
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
    if ( !$q->contains('LEFT JOIN p.Organism org') )
      $q->leftJoin('p.Organism org');
    $q->andWhere('org.id = ?', $value);
    
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

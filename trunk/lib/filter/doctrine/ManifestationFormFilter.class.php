<?php

/**
 * Manifestation filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationFormFilter extends BaseManifestationFormFilter
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['organizers_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->widgetSchema['event_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Event',
      'url'   => url_for('event/ajax'),
    ));
    
    $this->widgetSchema['happens_at']->setOption('template', '<span class="from">'.__('From %from_date%').'</span> <span class="to">'.__('to %to_date%').'</span>');
    
    $this->widgetSchema['location_id']->setOption('order_by', array('place DESC, name',''));
    
    $this->widgetSchema ['meta_event_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'MetaEvent',
      'order_by'  => array('name',''),
      'multiple'  => true,
      'query'     => $q = Doctrine::getTable('MetaEvent')->createQuery('me')->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())),
    ));
    $this->validatorSchema['meta_event_id'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'MetaEvent',
      'multiple'  => true,
      'query'     => $q,
      'required'  => false,
    ));
    
    $this->widgetSchema   ['workspace_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Workspace',
      'order_by'  => array('name',''),
      'multiple'  => true,
      'query'     => $q = Doctrine::getTable('Workspace')->createQuery('ws')->andWhereIn('ws.id',array_keys(sfContext::getInstance()->getUser()->getWorkspacesCredentials())),
    ));
    $this->validatorSchema['workspace_id'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'Workspace',
      'multiple'  => true,
      'query'     => $q,
      'required'  => false,
    ));
  }

  public function getFields()
  {
    return array_merge(parent::getFields(),array(
      'meta_event_id'   => 'MetaEventId',
      'workspace_id'    => 'WorkspaceId',
    ));
  }
  
  public function addLocationIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'location_id' )
      return $q;
    
    $a = $q->getRootAlias();
    $q->andWhere("$a.$field = ? OR $a.id IN (SELECT lb.manifestation_id FROM LocationBooking lb WHERE lb.location_id = ?)",array($value, $value));
    
    return $q;
  }
  
  public function addMetaEventIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'meta_event_id' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( is_array($value) )
      $q->andWhereIn("e.$field", $value);
    else
      $q->andWhere("e.$field = ?", $value);
    
    return $q;
  }
  
  public function addWorkspaceIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'workspace_id' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( is_array($value) )
      $q->andWhereIn("g.$field", $value);
    else
      $q->andWhere("g.$field = ?", $value);
    
    return $q;
  }
}

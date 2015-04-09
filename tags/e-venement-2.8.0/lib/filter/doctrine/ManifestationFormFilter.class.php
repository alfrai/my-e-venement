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
    
    $this->widgetSchema['location_id']->setOption('order_by', array('l.place DESC, l.rank, l.name',''))
      ->setOption('query', Doctrine::getTable('Location')->retrievePlaces());
    $this->widgetSchema['booking_list']->setOption('order_by', array('place ASC, name',''));
    
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
    
    $this->widgetSchema['color_id']->setOption('method', 'getName');
    
    $this->widgetSchema   ['has_description'] =
    $this->widgetSchema   ['has_extra_infos'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('' => 'yes or no', 1 => 'yes', 0 => 'no'),
    ));
    $this->validatorSchema['has_extra_infos'] =
    $this->validatorSchema['has_description'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
    
    $this->widgetSchema   ['participants_list']->setOption('query', Doctrine::getTable('Contact')->createQuery('c')
      ->leftJoin('c.InvolvedIn ii')
      ->andWhere('ii.id IS NOT NULL')
    );
    $this->validatorSchema['participants_list']->setOption('query', $this->widgetSchema['participants_list']->getOption('query'));
  }

  public function getFields()
  {
    $arr = parent::getFields();
    unset($arr['location_id']);
    return array_merge($arr,array(
      'meta_event_id'   => 'MetaEventId',
      'workspace_id'    => 'WorkspaceId',
      'has_extra_infos' => 'HasExtraInfos',
      'has_description' => 'HasDescription',
      'location_id'     => 'LocationId',
    ));
  }
  
  public function addLocationIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'location_id' )
      return $q;
    
    $a = $q->getRootAlias();
    
    if ( !$q->contains("LEFT JOIN $a.LocationBooking LocationBooking") )
      $q->leftJoin("$a.LocationBooking LocationBooking");
    $q->andWhere('(TRUE')
      ->andWhere("$a.$field = ?",$value)
      ->orWhere("LocationBooking.$field = ?",$value)
      ->andWhere('TRUE)');
    
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
  
  public function addHasDescriptionColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value === '0' )
      $q->andWhere("$a.description IS NULL OR trim($a.description) = ?",'');
    elseif ( $value === '1' )
      $q->andWhere("$a.description IS NOT NULL AND trim($a.description) != ?",'');
    
    return $q;
  }
  public function addHasExtraInfosColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value === '0' )
      $q->andWhere("$a.id NOT IN (SELECT DISTINCT mei.manifestation_id FROM ManifestationExtraInformation mei)");
    elseif ( $value === '1' )
      $q->andWhere("$a.id IN (SELECT DISTINCT mei.manifestation_id FROM ManifestationExtraInformation mei)");
    
    return $q;
  }
}

<?php

/**
 * EventTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class EventTable extends PluginEventTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object EventTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Event');
    }

  public function createQuery($alias = 'a')
  {
    $me  = 'me'   != $alias ? 'me'   : 'me1';
    $ec  = 'ec'   != $alias ? 'ec'   : 'ec1';
    $m   = 'm'    != $alias ? 'm'    : 'm1';
    
    return parent::createQuery($alias)
      ->leftJoin("$alias.MetaEvent $me")
      ->leftJoin("$alias.EventCategory $ec")
    ;
  }
  
  public function getOnlyGroupEvents()
  {
    return $this->createQuery('e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('w.GroupWorkspace gw')
      ->andWhere('gw.id IS NOT NULL');
  }
  
  public function retrieveList()
  {
    $cid = 0;
    $admin = false;
    $sf_user = false;
    try {
    if ( sfContext::hasInstance() && method_exists(sfContext::getInstance()->getUser(), 'getContactId') )
    {
      $sf_user = sfContext::getInstance()->getUser();
      $cid = $sf_user->getContactId();
      $admin = $sf_user->hasCredential('event-access-all');
    } }
    catch ( liOnlineSaleException $e )
    { }
    
    $q = $this->createQuery('e')
      ->select('e.*, ec.*, me.*, m.*, l.*, c.*, g.*')
      ->addSelect('(SELECT max(mm2.happens_at) AS max FROM Manifestation mm2 WHERE mm2.event_id = e.id) AS max_date')
      ->addSelect('(SELECT min(mm3.happens_at) AS min FROM Manifestation mm3 WHERE mm3.event_id = e.id) AS min_date')
      ->leftJoin('e.Manifestations m ON m.event_id = e.id AND (m.reservation_confirmed = TRUE '.
        (!is_null($cid) ? 'OR m.contact_id = '.$cid.' OR '.($admin ? 'TRUE' : 'FALSE') : '')
      .')')
      ->leftJoin('m.Color c')
      ->leftJoin('m.Gauges g')
      ->leftJoin('m.Location l')
    ;
    
    if ( $sf_user )
    $q->andWhereIn('g.workspace_id IS NULL OR g.workspace_id', array_keys($sf_user->getWorkspacesCredentials()))
      ->andWhereIn('e.meta_event_id', array_keys($sf_user->getMetaEventsCredentials()))
    ;
    
    return $q;
  }
  public function retrievePublicList()
  {
    return $this->retrieveList()
      ->andWhere('g.online = TRUE')
      ->andWhere('m.reservation_confirmed = TRUE')
      ->andWhere('m.happens_at > NOW()');
  }
}

<?php

class myUser extends liGuardSecurityUser
{
  const CREDENTIAL_METAEVENT_PREFIX = 'event-metaevent-';
  const CREDENTIAL_WORKSPACE_PREFIX = 'event-workspace-';
  
  protected $metaevents = array();
  protected $workspaces = array();
  
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);
    if (is_null($this->authenticated))
      return;
    
    $this->addCredentials($this->getWorkspacesCredentials());
    $this->addCredentials($this->getMetaEventsCredentials());
    
    if ( $this->getAttribute('store', NULL) === NULL
      || time() > strtotime($this->getAttribute('store_timeout', NULL)) )
    {
      $q = Doctrine::getTable('ProductCategory')->createQuery('pc')
        ->leftJoin('pc.Products p')
        ->andWhereIn('p.meta_event_id IS NULL OR p.meta_event_id', array_keys($this->getMetaEventsCredentials()))
        ->andWhere('p.id IS NOT NULL')
        ->leftJoin('p.Declinations d')
        ->andWhere('d.id IS NOT NULL')
      ;
      $store = $q->count() > 0;
      $this->setAttribute('store', $store);
      $this->setAttribute('store_timeout', date('Y-m-d H:i:s', strtotime('+5 minutes')));
    }
  }
  
  public function isStoreActive()
  {
    return $this->getAttribute('store', false);
  }
  
  public function getWorkspacesCredentials()
  {
    $this->getGuardUser();
    if ( $this->workspaces )
      return $this->workspaces;
    
    $this->workspaces = array();
    
    if ( !$this->user )
      return $this->workspaces;
    
    foreach ( $this->user->Workspaces as $ws )
      $this->workspaces[$ws->id] = myUser::CREDENTIAL_WORKSPACE_PREFIX.$ws->id;
    
    return $this->workspaces;
  }
  public function getMetaEventsCredentials()
  {
    $this->getGuardUser();
    if ( $this->metaevents )
      return $this->metaevents;
    
    $this->metaevents = array();
    
    if ( !$this->user )
      return $this->metaevents;
    
    foreach ( $this->user->MetaEvents as $me )
      $this->metaevents[$me->id] = myUser::CREDENTIAL_METAEVENT_PREFIX.$me->id;
    
    return $this->metaevents;
  }
}

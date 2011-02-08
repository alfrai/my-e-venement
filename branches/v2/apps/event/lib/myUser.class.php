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
    $this->addCredentials($this->getMetaEventCredentials());
  }
  
  public function getWorkspacesCredentials()
  {
    $this->getGuardUser();
    if ( $this->workspaces )
      return $this->workspaces;
    
    $this->workspaces = array();
    
    foreach ( $this->user->Workspaces as $ws )
      $this->workspaces[$ws->id] = myUser::CREDENTIAL_WORKSPACE_PREFIX.$ws->id;
    
    return $this->workspaces;
  }
  public function getMetaEventCredentials()
  {
    $this->getGuardUser();
    if ( $this->metaevents )
      return $this->metaevents;
    
    $this->metaevents = array();
    
    foreach ( $this->user->MetaEvents as $me )
      $this->metaevents[$me->id] = myUser::CREDENTIAL_METAEVENT_PREFIX.$me->id;
    
    return $this->metaevents;
  }
}

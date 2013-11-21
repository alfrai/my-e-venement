<?php

class myUser extends liGuardSecurityUser
{
  const CREDENTIAL_METAEVENT_PREFIX = 'event-metaevent-';
  const CREDENTIAL_WORKSPACE_PREFIX = 'event-workspace-';
  
  protected $metaevents = array();
  protected $workspaces = array();
  protected $force_contact_id = false;
  
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);
    if (is_null($this->authenticated))
      return;
    
    $this->addCredentials($this->getWorkspacesCredentials());
    $this->addCredentials($this->getMetaEventsCredentials());
  }
  
  /**
   * if set true, then the getContactId() function will return NULL in the place of the user's contact id (or 0 if none)
   **/
  public function forceContact($bool = true)
  {
    $this->force_contact_id = $bool;
    return $this;
  }
  public function getContactId()
  {
    if ( $this->force_contact_id )
      return NULL;
    return parent::getContactid();
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

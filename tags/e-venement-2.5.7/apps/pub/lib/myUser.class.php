<?php

class myUser extends liGuardSecurityUser
{
  const CREDENTIAL_METAEVENT_PREFIX = 'event-metaevent-';
  const CREDENTIAL_WORKSPACE_PREFIX = 'event-workspace-';
  
  protected $metaevents = array();
  protected $workspaces = array();
  protected $transaction = NULL;
  
  public function getGuardUser()
  {
    if (!$this->user )
      $this->user = Doctrine::getTable('sfGuardUser')->retrieveByUsername(sfConfig::get('app_user_templating'));
    
    if (!$this->user)
    {
      // the user does not exist anymore in the database
      $this->signOut();
      
      throw new sfException('The user does not exist anymore in the database.');
    }
    
    return $this->user;
  }

  public function getWorkspacesCredentials() {
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
  
  public function getContact()
  {
    if ( is_null($this->getTransaction()->contact_id) )
      throw new liOnlineSaleException('Not yet authenticated.');
    
    return $this->getTransaction()->Contact;
  }
  public function hasContact()
  {
    return !is_null($this->getTransaction()->contact_id);
  }
  public function setContact(Contact $contact)
  {
    if ( !$contact->id )
      throw new liOnlineSaleException('Your contact is not yet recorded or does not fit the system requirements');
    
    $this->getTransaction()->Contact = $contact;
    foreach ( $this->getTransaction()->MemberCards as $mc )
      $mc->Contact = $contact;
    $this->getTransaction()->save();
    return $this;
  }
  
  public function getTransaction()
  {
    if ( !$this->hasAttribute('transaction_id') )
    {
      $this->transaction = new Transaction;
      $this->transaction->save();
      $this->setAttribute('transaction_id',$this->transaction->id);
    }
    
    if ( $this->transaction instanceof Transaction )
      return $this->transaction;
      
    $q = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('t.MemberCards tmc')
      ->leftJoin('t.Order o')
      ->leftJoin('tmc.MemberCardPrices tmcp')
      ->leftJoin('t.Contact c')
      ->leftJoin('c.Transactions tr')
      ->leftJoin('c.MemberCards cmc ON c.id = cmc.contact_id AND (cmc.active = TRUE OR cmc.transaction_id = t.id)')
      ->leftJoin('cmc.MemberCardPrices cmcp')
      ->andWhere('t.id = ?',$this->getAttribute('transaction_id'));
    
    return $this->transaction = $q->fetchOne();
  }
  
  public function resetTransaction()
  {
    try { $contact = $this->getContact(); }
    catch ( liOnlineSaleException $e ) { }
    
    $this->logout();
    if ( isset($contact) )
      $this->setContact($contact);
  }
  public function logout()
  {
    $this->getAttributeHolder()->remove('transaction_id');
    $this->transaction = NULL;
    $this->getTransaction();
  }
}

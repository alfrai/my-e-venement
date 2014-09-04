<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

class myUser extends liGuardSecurityUser
{
  const CREDENTIAL_METAEVENT_PREFIX = 'event-metaevent-';
  const CREDENTIAL_WORKSPACE_PREFIX = 'event-workspace-';
  
  protected $metaevents = array();
  protected $workspaces = array();
  protected $transaction = NULL;
  
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);
    $dispatcher->connect('pub.pre_execute', array($this, 'mustAuthenticate'));
  }
  
  public function mustAuthenticate(sfEvent $event)
  {
    if ( !sfConfig::get('app_user_must_authenticate', false) )
      return;
    
    // for plateforms that require authenticated visitors
    $sf_action = $event->getSubject();
    if (!( method_exists($sf_action, 'isAuthenticatingModule') && $sf_action->isAuthenticatingModule() ))
      $sf_action->redirect('login/index');
  }
  
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
      $this->dispatcher->notify(new sfEvent($this, 'pub.transaction_before_creation', array(
        'transaction' => $this->transaction,
        'user' => $this,
      )));
      
      $this->transaction->save();
      $this->setAttribute('transaction_id',$this->transaction->id);
      
      $this->dispatcher->notify(new sfEvent($this, 'pub.transaction_after_creation', array(
        'transaction' => $this->transaction,
        'user' => $this,
      )));
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
    catch ( liOnlineSaleException $e ) { error_log($e->getMessage()); }
    
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

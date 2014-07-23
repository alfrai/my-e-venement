<?php

/**
 * liCardDavPlugin configuration.
 * 
 * @package     liCardDavPlugin
 * @subpackage  config
 * @author      Baptiste SIMON
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class liCardDavPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '2.6-pre6';
  protected $dispatcher = NULL;
  
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    require_once __DIR__.'/../lib/vendor/SabreDAV/lib/Sabre/autoload.php';
    
    // the events
    $this->dispatcher->connect('admin.save_object',     array($this, 'listenToSaveObject'));
    $this->dispatcher->connect('admin.delete_object',   array($this, 'listenToDeleteObject'));
    $this->dispatcher->connect('admin.delete_objects',  array($this, 'listenToDeleteObjects'));
  }
  
  /**
   * sends the saved data into the DAV directory
   **/
  public function listenToSaveObject(sfEvent $event)
  { try
  {
    if ( !$this->canBeSynchronized($event) )
      return;
    
    // the webdav connection
    $davcon = $this->initDavConnection();
    
    // save the object
    $davcard = liCardDavVCard::create($davcon, $event['object']->vcard_uid, (string)$event['object']->vcard);
    $davcard->save();
  }
  catch ( Exception $e )
  {
    $this->log($e);
  } }
  
  /**
   * delete the deleted object in the DAV directory
   **/
  public function listenToDeleteObject(sfEvent $event)
  { try
  {
    if ( !$this->canBeSynchronized($event)
      || !$event['object']->vcard_uid )
      return;
    
    // the webdav connection
    $davcon = $this->initDavConnection();
    
    // delete the object
    $davcon
      ->getVCard($event['object']->vcard_uid)
      ->delete();
  }
  catch ( Exception $e )
  {
    $this->log($e);
  } }
  
  /**
   * delete the deleted objects in the DAV directory
   **/
  public function listenToDeleteObjects(sfEvent $event)
  { try
  {
    if ( !$this->canBeSynchronized($event, true) )
      return;
    if (! ($event['objects']->count() > 0
        && ($event['objects'][0] instanceof Contact || $event['objects'][0] instanceof Organism) ))
      return;
    
    // the webdav connection
    $davcon = $this->initDavConnection();
    
    // delete the object
    foreach ( $event['objects'] as $object )
    if ( $object->vcard_uid )
    $davcon
      ->getVCard($object->vcard_uid)
      ->delete();
  }
  catch ( Exception $e )
  {
    $this->log($e);
  } }
  
  /**
   * creates the DAV connection
   * @return liCardDavConnection
   **/
  protected function initDavConnection()
  {
    return new liCardDavConnectionZimbra(sfConfig::get('app_carddav_sync_cards_url'), sfConfig::get('app_carddav_sync_auth'));
  }
  
  /**
   * Tests for preconditions to any carddav sync
   * @param $event the current event
   * @param $only_config whether or not to control the given object or to limit the verification to the software configuration (optional, false by default)
   * @return TRUE if everything's ready
   * @return otherwize FALSE
   **/
  protected function canBeSynchronized(sfEvent $event, $only_config = false)
  {
    // preconditions
    if ( !$only_config )
    if ( !isset($event['object'])
      || !$event['object'] instanceof Contact
      && !$event['object'] instanceof Organism )
      return false;
    
    if ( !sfConfig::get('app_carddav_sync_auth',false)
      || !sfConfig::get('app_carddav_sync_cards_url',false) )
      return false;
    
    return true;
  }
  
  /**
   * returns the dispatcher
   * @return sfEventDispatcher
   **/
  public function getDispatcher()
  {
    return $this->dispatcher;
  }
  
  /**
   * Function that helps making dispatcher calls fail-proof
   * @param $e Exception
   * @return void
   **/
  public function log(Exception $e)
  {
    if ( sfContext::hasInstance() && sfConfig::get('sf_debug') )
      error_log($e);
    else
      error_log($e->getMessage());
  }
}

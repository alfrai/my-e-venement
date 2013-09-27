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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class SynchronizeContactsTask extends sfBaseTask{

  protected $weirds = array();

  protected function configure() {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'prod'),
      new sfCommandOption('sync', null, sfCommandOption::PARAMETER_REQUIRED, 'The sync direction (both by default or dav2e or e2dav)', 'both'),
      new sfCommandOption('nb', null, sfCommandOption::PARAMETER_REQUIRED, 'The number of contacts you want to synchronize (mainly for tests purposes, 0 = no limit)', '0'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Display debug informations'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Force complete upload to the DAV repository (use with precaution, can take a loooong time)'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'synchronize-contacts';
    $this->briefDescription = "Synchronize your e-venement's contacts with your distant CardDAV plateform";
    $this->detailedDescription = <<<EOF
      The [sc:synchronize-contacts|INFO] synchronizes your e-venement's contacts with a distant CardDAV plateform:
      [./symfony e-venement:synchronize-contacts --env=dev --sync=e2dav --application=rp|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // prerequiresites
    sfApplicationConfiguration::getActive()->loadHelpers(array('MultiByte'));
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance($this->configuration,$options['env']);
    
    if ( !sfConfig::get('app_carddav_sync_auth', array()) || !sfConfig::get('app_carddav_sync_contacts_url','') )
      throw new sfCommandException(printf('The %s application is not configured for CardDAV features', $option['application']));
    
    $con = new liCardDavConnectionZimbra(sfConfig::get('app_carddav_sync_contacts_url'), sfConfig::get('app_carddav_sync_auth'), sfConfig::get('app_carddav_sync_options',array()));
    $con->isValid();
    $this->logSection('connection','Connected');
    $this->logSection('last-update',$con->getLastUpdate());
    
    if ( in_array($options['sync'], array('both', 'e2dav')) )
      $this->e2dav($con, $options);
    if ( in_array($options['sync'], array('both', 'dav2e')) )
      $this->dav2e($con, $options);
  }
  
  /**
   * function dav2e imports CardDAV service's data into e-venement
   *
   * @param $con, the liCardDavConnection object
   * @param $option, the task options from the execute() function
   *
   **/
  protected function dav2e(liCardDavConnection $con, array $options)
  {
  }

  /**
   * function e2dav exports e-venement's data into the CardDAV service
   *
   * @param $con, the liCardDavConnection object
   * @param $option, the task options from the execute() function
   *
   **/
  protected function e2dav(liCardDavConnection $con, array $options)
  {
    $cpt = array(
      'up2date' => 0,
      'uploaded' => 0,
      'added' => 0,
      'deleted' => 0,
    );
    
    $q = Doctrine::getTable('Contact')->createQuery('c')
      ->limit(isset($options['nb']) ? intval($options['nb']) : 0)
      ->andWhere('p.id IS NULL')
      ->orderBy('c.created_at, c.updated_at DESC')
      ;
    if (!( isset($options['force']) && $options['force'] ))
      $q->andWhere('c.updated_at > ?', date('Y-m-d H:i:s', strtotime($con->getLastUpdate())));
    
    $i = 0;
    foreach ( $q->execute() as $contact )
    {
      $nb = str_pad(++$i,5,'0',STR_PAD_RIGHT);
      $contact_str = mb_str_pad($contact, 30);
      
      $vcard = array('e' => new liCardDavVCard($con, $contact->vcard_uid, (string)$vc = new liVCardContact($contact, array('timezone_hack' => true,))));
      
      // try to stop the process if the distant data is up2date or exists in a newer version
      if ( $contact->vcard_uid )
      {
        $vcard['dav'] = new liCardDavVCard($con, $contact->vcard_uid);
        
        if ( isset($vcard['dav']['rev']) && strtotime($vcard['dav']['rev']) >= strtotime($vcard['e']['rev']) )
        {
          $cpt['up2date']++;
          $this->weirds[] = $contact;
          $this->logSection('e2dav', sprintf('%s Contact %s has been kept (uid %s)', $nb, $contact_str, $contact->vcard_uid), null, 'COMMAND');
          
          // debug
          if ( $options['debug'] )
          {
            echo sprintf("distant: %s/%s >= local: %s/%s\n\n", $vcard['dav']['rev'], strtotime($vcard['dav']['rev']), $vcard['e']['rev'], strtotime($vcard['e']['rev']));
            echo $vcard['dav']."\n";
          }
          
          continue;
        }
      }
      
      // local data needs to be sent to the CardDAV repository -> create or update
      if ( $options['env'] != 'dev' ) // PROD ENV - for real
      {
        // try to delete to fake updating
        $deleted = true;
        try
        {
          if ( isset($vcard['dav']['uid']) )
            $vcard['dav']->delete();
          else
          {
            $vcard['e']->turnNew();
            $deleted = false;
          }
        }
        catch ( liCardDavResponse404Exception $e )
        { $delete = false; }
        
        // adding the object
        $response = $vcard['e']->save();
        $contact->vcard_uid = $response->getUid();
        $contact->save();
        
        $cpt[$deleted ? 'uploaded' : 'added']++;
        $this->logSection('e2dav', sprintf('%s Contact %s has been sent (uid %s)', $nb, $contact_str, $contact->vcard_uid), null, 'COMMAND');
      }
      else // DEVELOPMENT ENV - tests only
      {
        // check if the object exists, so would be deleted
        $delete = true;
        try { $vcard['dav']->update(); }
        catch ( liCardDavResponse404Exception $e )
        { $delete = false; }
        
        $cpt[$deleted ? 'uploaded' : 'added']++;
        $this->logSection('e2dav', sprintf('%s Contact %s has not been sent (uid %s)', $nb, $contact_str, $contact->vcard_uid), null, 'ERROR');
      }
      
      // debug code
      if ( $options['debug'] )
      {
        if ( isset($vcard['dav']) )
          echo sprintf("distant: %s/%s < local: %s/%s\n\n", $vcard['dav']['rev'], strtotime($vcard['dav']['rev']), $vcard['e']['rev'], strtotime($vcard['e']['rev']));
        echo $vcard['e']."\n";
      }
      
    }
    
    // DELETE vCards already deleted on e-venement
    $table_name = 'sync_'.time(); // prepare a temporary table
    $ids = $con->getIdsList();    // gets the remaining ids
    if ( count($ids) > 0 )
    {
      $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
      
      // creates the temp table
      $stmt = $pdo->prepare("CREATE TEMP TABLE $table_name (id TEXT PRIMARY KEY);");
      $res = $stmt->execute();
      
      // inserts the ids
      foreach ( $ids as $id )
        $pdo->prepare("INSERT INTO $table_name VALUES ('$id');")->execute();

      // retrieving deleted contacts
      $q = "SELECT id FROM $table_name WHERE id NOT IN (SELECT vcard_uid FROM contact WHERE vcard_uid IS NOT NULL)";
      $stmt = $pdo->prepare($q);
      $stmt->execute();
      
      // deleting foreign data
      foreach ( $stmt->fetchAll() as $uid )
      {
        $nb = str_pad(++$i,5,'0',STR_PAD_RIGHT);
        $vcard = NULL;
        $vcard = $con->getVCard($uid['id']);
        $contact_str = mb_str_pad($vcard['fn'], 30);
        $this->logSection('e2dav', sprintf('%s Contact %s has been deleted (uid %s)', $nb, $contact_str, $uid['id']), null, 'COMMAND');
        
        $vcard->delete();
        $cpt['deleted']++;
      }
    }
    
    $this->logSection('e2dav', sprintf('%d contact(s) added into the DAV repository', $cpt['added']));
    $this->logSection('e2dav', sprintf('%d contact(s) that have been updated in the DAV repository', $cpt['uploaded']));
    $this->logSection('e2dav', sprintf('%d contact(s) that did not need any synchronization', $cpt['up2date']));
    $this->logSection('e2dav', sprintf('%d contact(s) have been deleted from the DAV repository', $cpt['deleted']));
    
    $con->resetLastUpdate();
    $this->logSection('last-update',$con->getLastUpdate());
    
    return $this;
  }
}

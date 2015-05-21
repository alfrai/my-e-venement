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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class MasterPingTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('action', sfCommandArgument::REQUIRED, 'The normal usage requires "ping", and to finish properly the process, it must be "end"'),
      new sfCommandArgument('application', sfCommandArgument::OPTIONAL, 'The application', 'ws'),
    ));
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'prod'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'master-ping';
    $this->briefDescription = 'Inserts a "ping" into the database to let the MASTER instance know when a slave is disconnected.';
    $this->detailedDescription = <<<EOF
      The [mp:master-ping|INFO] executes a ping for the master:
      [./symfony e-venement:master-ping --env=prod tck [wip]|INFO]
      Eventually it can trigger the "failover" mechanism on the current host.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    if ( !in_array($arguments['action'], array('end', 'ping')) )
    {
      $this->logSection('action', sprintf('"%s" is invalid and unacceptable.', $arguments['action']), null, 'ERROR');
      return false;
    }
    
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $ping = new SlavePing;
    $ping->state = $arguments['action'];
    $ping->created_at = date('Y-m-d H:i:s');
    
    try {
      $ping->save();
      $this->logSection('ping', sprintf('Ping "%s" done...', $arguments['action']));
    } catch ( Doctrine_Exception $e ){
      $this->logSection('ping', sprintf('Ping "%s" failed!! (%s)', $arguments['action'], $e->getMessage()), null, 'ERROR');
      $this->tryFailover();
    }
  }
  
  protected function tryFailover()
  {
    $file = sfConfig::get('sf_cache_dir').'/ping.touch';
    if ( !file_exists($file) )
      touch($file);
    
    if ( filemtime($file) < strtotime(sfConfig::get('app_failover_timeout', '2 minutes 30 seconds').' ago') - 10 ) // 2 min 20 sec ago
      $this->failover();
    
    return $this;
  }
  
  protected function failover()
  {
    $this->log('ping', 'Last ping was more than '.sfConfig::get('app_failover_timeout', '2 minutes 30 seconds').' ago... Triggerring the failover mecanism for SLAVE systems.');
    $triggers = sfConfig::get('app_failover_triggers', array());
    if ( !isset($triggers['slave']) )
      return $this;
    
    // the trigger for PostgreSQL
    touch($triggers['slave']);
    
    // the trigger for e-venement
    $yaml = sfYaml::load($path = sfConfig::get('sf_config_dir').'/databases.yml');
    foreach ( $yaml['all'] as $name => $data )
    if ( isset($data['param']['is_master']) || $name == 'master' )
    {
      unset($yaml['all'][$name]);
      break;
    }
    file_put_contents($path, sfYaml::dump($yaml));
    $this->runTask('cache:clear');
    touch(sfConfig::get('sf_cache_dir').'/e-venement.norsync.trigger');
    
    // print out that is happened
    $this->logSection('failover', 'The triggers of the failover mechanism have been pulled!!! (including the removal of MASTER DB connection)');
    
    return $this;
  }
}

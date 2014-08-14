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
class GarbageCollectorTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application'),
      new sfCommandArgument('subtask', sfCommandArgument::OPTIONAL, 'The email to take as a template'),
    ));
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'prod'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'garbage-collector';
    $this->briefDescription = 'Executes garbage collectors for an application';
    $this->detailedDescription = <<<EOF
      The [gc:garbage-collector|INFO] Executes garbage collectors for an application:
      [./symfony e-venement:garbage-collector --env=prod tck [wip]|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $arguments['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    if (! $this->configuration instanceof liGarbageCollectorInterface )
      throw new sfCommandException('The application configuration does not permit garbage collection');
    
    $this->configuration->initGarbageCollectors($this);
    $this->configuration->executeGarbageCollectors($arguments['subtask']);
  }
}

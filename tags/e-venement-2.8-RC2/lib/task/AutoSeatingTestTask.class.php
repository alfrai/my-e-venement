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
class AutoSeatingTestTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('test', sfCommandArgument::REQUIRED, 'The test to run [find|orphan]'),
      new sfCommandArgument('gauge', sfCommandArgument::REQUIRED, 'The gauge to check'),
      new sfCommandArgument('qty', sfCommandArgument::OPTIONAL, 'How many seats are you looking for ?', 2),
    ));
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'event'),
      new sfCommandOption('test-orphan', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of one seat'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'auto-seating-test';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $seater = new Seater($arguments['gauge']);
    
    switch ( $arguments['test'] ) {
    case 'find':
      $seats = $seater->findSeats($arguments['qty']);
      if ( $seats->count() > 0 )
      foreach ( $seats as $seat )
        $this->logSection('Find', 'Found seat '.$seat->name.' with rank '.$seat->rank);
      else
        $this->logSection('Find', 'No seat combination has been found to satisfy this quantity');
      break;
    case 'orphan':
      if ( $options['test-orphan'] )
      $this->logSection('Orphan', 'Tested seat: '.$options['test-orphan']);
      
      $orphans = $seater->findOrphansWith($options['test-orphan'] ? $options['test-orphan'] : $seater->findSeats($arguments['qty']));
      
      if ( $orphans->count() > 0 )
      foreach ( $orphans as $seat )
        $this->logSection('Orphan', $seat->name.' would be an orphan...',null,'ERROR');
      else
        $this->logSection('Orphan', 'Congrats... no orphan found!');
      break;
    }
  }
}

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
class SearchIndexTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The Model'),
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'default'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Force index rebuilding'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'search-index';
    $this->briefDescription = 'Updates the searchable data of the given model';
    $this->detailedDescription = <<<EOF
      The [si:search-index|INFO] Updates the searchable data of a model:
      [./symfony e-venement:search-index model --env=dev|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    if(!class_exists($arguments['model']))
      throw new sfCommandException(sprintf('Model "%s" doesn\'t exist.', $arguments['model']));
    
    $modelTable = Doctrine_Core::getTable($arguments['model']);
    sfContext::createInstance($this->configuration,'dev');
    $modelTable
      ->getTemplate('Doctrine_Template_Searchable')
      ->getPlugin()
      ->setOption('analyzer', new MySearchAnalyzer());
    
    if ( $options['force'] )
    {
      $q = new Doctrine_Query;
      $q->from($arguments['model'].'Index')
        ->delete()
        ->execute();
    }
    
    $nb = $modelTable->batchUpdateIndex();
    $this->logSection('search', sprintf('%s %s updated', $nb, $arguments['model']));
  }
}

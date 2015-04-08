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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class CopyI18nTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The model to process (ex: Price)'),
      new sfCommandArgument('from', sfCommandArgument::REQUIRED, 'The i18n translation to copy (ex: fr)'),
      new sfCommandArgument('to', sfCommandArgument::REQUIRED, 'The i18n destination (ex: en)'),
    ));
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'default'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'copy-i18n';
    $this->briefDescription = 'Copy a translation from a culture to another for a given model';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $model = Doctrine::getTable($arguments['model']);
    foreach ( $model->createQuery('m')->execute() as $model )
    if ( $model->hasRelation('Translation')
      && isset($model->Translation[$arguments['from']])
      && !isset($model->Translation[$arguments['to']])
    )
    {
      $table = $model->Translation[$arguments['from']]->getTable();
      
      $fields = array();
      foreach ( $table->getFieldNames() as $fieldname )
      if ( !$table->isIdentifier($fieldname) )
      {
        $fields[] = $fieldname;
        $model->Translation[$arguments['to']]->$fieldname = $model->Translation[$arguments['from']]->$fieldname;
      }
      
      if ( $model->trySave() )
        $this->logSection('Translation', '"'.$arguments['to'].'" translation created for price "'.$model.'" (fields: '.implode(', ', $fields).').');
      else
        $this->logSection('Translation', '"'.$arguments['to'].'" translation failed for price "'.$model.'"', null, 'ERROR');
    }
  }
}

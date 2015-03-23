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
class GeolocalizeTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The Model'),

      )
    );
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev')
    ));
    $this->namespace = 'e-venement';
    $this->name = 'geocode';
    $this->briefDescription = 'Updates the addressable model\'s geographical data';
    $this->detailedDescription = <<<EOF
      The [geo:geocode|INFO] Updates the geographical data of an addressable model:
      [./symfony e-venement:geocode model --env=dev|INFO]
EOF;
  }

  protected function  execute($arguments = array(), $options = array()) {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $modelTableClass = sfInflector::classify($arguments['model'].'_table');

    if(!class_exists($modelTableClass)){
      throw new sfCommandException(sprintf('Model "%s" doesn\'t exist.', $arguments['model']));
    }else{
      //récupérer les enregistrements
      $modelTable = Doctrine_Core::getTable($arguments['model']);
      if($modelTable instanceof AddressableTable){
      $records = $modelTable->findAll();
      
        if($records){
          if ( $records->count() == 0 )
            $this->logSection('geo', sprintf('No record to be updated'));
          else
          foreach ($records as $record){

            try
            {
              //$record->updateGeolocalization();
              $record->save();
              $this->logSection('geo', sprintf('%s %s updated', $arguments['model'],$record->getId()));
            }
            catch ( sfException $e )
            {
              $this->logSection('geo', sprintf('ERROR on %s %s', $arguments['model'],$record->getId()));
            }
          }

        }else{
          throw new sfCommandException(sprintf("Model \"%s\" doesn't contain any record.", $arguments['model']));
        }
      }else{
        throw new sfCommandException(sprintf('Model "%s" is not an instance of the Adressable class.', $arguments['model']));
      }
    }
    
  }
}

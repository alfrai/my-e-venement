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
class AddPricesToAllWSTask extends sfBaseTask{

  protected function configure() {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'event'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev')
    ));
    $this->namespace = 'e-venement';
    $this->name = 'add-prices-to-all-ws';
    $this->briefDescription = 'Associates all prices to all workspaces';
    $this->detailedDescription = <<<EOF
      The [aptaw:add-prices-to-all-ws|INFO] Updates all prices to link them to all workspaces. Very usefull in schema migrations and so on:
      [./symfony e-venement:geocode model --env=dev|INFO]
EOF;
  }

  protected function  execute($arguments = array(), $options = array()) {
    $databaseManager = new sfDatabaseManager($this->configuration);

    if(!class_exists('PriceTable') || !class_exists('WorkspaceTable')){
      throw new sfCommandException(sprintf('Model "%s" or "%s" doesn\'t exist.','PriceTable','WorkspaceTable'));
    }else{
      //récupérer les enregistrements
      $prices = Doctrine::getTable('Price')->findAll();
      $ws = Doctrine::getTable('Workspace')->findAll();
      
      if($prices)
      {
        if ( $prices->count() == 0 )
          $this->logSection('aptaw', sprintf('No record to be updated'));
        else
         foreach ($prices as $price){
          try
          {
            foreach ( $ws as $w )
              $price->Workspaces[] = $w;
            $price->save();
            $this->logSection('aptaw', sprintf('%s "%s" updated', get_class($price), $price->name));
          }
          catch ( sfException $e )
          {
            $this->logSection('geo', sprintf('ERROR on %s %s', $arguments['model'],$price->name));
          }
        }
      }
      else
      {
        throw new sfCommandException(sprintf("Model \"%s\" doesn't contain any record.", 'Price'));
      }
    }
  }
}

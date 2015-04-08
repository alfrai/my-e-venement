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
class ManifsPricesTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev')
    ));
    $this->namespace = 'e-venement';
    $this->name = 'manifs-prices';
    $this->briefDescription = 'Adds default prices to manifestations';
    $this->detailedDescription = <<<EOF
      The [manifs:manifs-prices|INFO] Adds default prices to manifestations with no specific price at all.';
      [./symfony e-venement:manifs-prices --env=dev|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $prices = Doctrine::getTable('Price')->createQuery()->execute();
    
    $q = new Doctrine_Query();
    $q->from('Manifestation m')
      ->andWhere('m.id NOT IN (SELECT DISTINCT pm.manifestation_id FROM PriceManifestation pm)');
    
    foreach ( $q->execute() as $manifestation )
    {
      foreach ( $prices as $price )
      {
        $pm = PriceManifestation::createPrice($price);
        $pm->manifestation_id = $manifestation->id;
        $pm->save();
      }
      $this->logSection('manif', sprintf(
        'Manifestation %s updated with %s prices',
        '"'.$manifestation->Event->name.'" @ '.$manifestation->happens_at,
        $prices->count()
      ));
    }
  }
}

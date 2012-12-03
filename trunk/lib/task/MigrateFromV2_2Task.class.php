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
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class MigrateFromV2_2Task extends sfBaseTask
{
  protected function configure() {
    $this->addArguments(array(
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('revert', null, sfCommandOption::PARAMETER_NONE, 'Getting the schema back !!WARNING!! may cause data loose.'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'migrate-from-v22';
    $this->briefDescription = 'Migrates the schema and the data from v2.2 to v2.3';
    $this->detailedDescription = <<<EOF
      The [mfv22:migrate-from-v22|INFO] Migrates the schema and the data from v2.2 to v2.3:
      [./symfony e-venement:migrate-from-v22 --application=rp --env=dev --revert|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    if ( $options['revert'] )
    {
      $migration = new Doctrine_Migration(sfConfig::get('sf_lib_dir').'/migration/from-v2.2/');
      $migration->migrate(0);
      $this->logSection('revert', 'Complete.');
    }
    
    $this->logSection('Step 0', 'Did you think rebuilding your model, forms and filters first ?');
    $this->logSection('Step 0', 'Did you think removing your previous migration files and dropping the migration_version table ?');
    
    // the schema, first
    $migration = new Doctrine_Migration(sfConfig::get('sf_lib_dir').'/e-venement/from-v2.2');
    $migration->migrate(1);
    $this->logSection('Step 1', 'Schema update.');
    
    // the data
    $pm = Doctrine::getTable('PaymentMethod')->createQuery('pm')
      ->andWhere('pm.member_card_linked = true')
      ->orderBy('pm.id ASC')
      ->limit(1)
      ->fetchOne();
    
    $cpt = 0;
    $types = sfConfig::get('app_cards_types');
    foreach ( $types as $type )
    {
      $mc = Doctrine::getTable('MemberCard')->createQuery('mc')
        ->leftJoin('mc.Payments p')
        ->andWhere('mc.name = ?',$type)
        ->andWhere('p.payment_method_id = ?',$pm->id)
        ->orderBy('id DESC')
        ->fetchOne();
      
      $mct = new MemberCardType;
      $mct->name = $type;
      $mct->value = 0;
      foreach ( $mc->Payments as $p )
        $mct->value -= $p->value;
      $mct->save();
      $cpt++;
      
      Doctrine_Query::create()->from('MemberCard mc')
        ->andWhere('mc.name = ?',$type)
        ->set('member_card_type_id',$mct->id)
        ->update()
        ->execute();
      Doctrine_Query::create()->from('MemberCardPriceModel mcpm')
        ->andWhere('LOWER(mcpm.member_card_name) = LOWER(?)',$type)
        ->set('member_card_type_id',$mct->id)
        ->update()
        ->execute();
    }
    
    $this->logSection('Step 2', $cpt.' member card type(s) created.');

    // finishing the schema's migration, removing useless data
    $migration->migrate(2);
    $this->logSection('Step 3', 'Schema update (irreversible).');
    $this->logSection('Step -', 'Empty your cookies from your e-venement application to be sure to avoid bugs');
  }
}

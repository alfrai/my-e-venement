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
// from v2.2 step 1
class Version1 extends Doctrine_Migration_Base
{
  public function down()
  {
    foreach ( array('member_card','member_card_price_model') as $table )
    {
      $this->dropForeignKey($table, $table.'_member_card_type_id_member_card_type_id');
      $this->removeIndex($table, $table.'_member_card_type_id');
      $this->removeColumn($table, 'member_card_type_id');
      $this->removeColumn($table.'_version', 'member_card_type_id');
    }
    
    $this->dropTable('member_card_type');
  }
  public function up()
  {
    $this->createTable('member_card_type', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '8',
              'autoincrement' => '1',
              'primary' => '1',
             ),
             'name' => 
             array(
              'type' => 'string',
              'unique' => '1',
              'notnull' => '1',
              'notblank' => '1',
              'length' => '',
             ),
             'value' => 
             array(
              'type' => 'integer',
              'notnull' => '1',
              'length' => '8',
             ),
             ), array(
             'primary' => 
             array(
              0 => 'id',
             ),
    ));
    foreach ( array('member_card','member_card_price_model') as $table )
    {
      $this->addColumn($table, 'member_card_type_id', 'integer', '8', array(
            ));
      $this->addColumn($table.'_version', 'member_card_type_id', 'integer', '8', array(
            ));
      $this->createForeignKey($table, $table.'_member_card_type_id_member_card_type_id', array(
            'name' => $table.'member_card_type_id_member_card_type_id',
            'local' => 'member_card_type_id',
            'foreign' => 'id',
            'foreignTable' => 'member_card_type',
            'onUpdate' => 'CASCADE',
            'onDelete' => 'RESTRICT',
      ));
      $this->addIndex($table, $table.'_member_card_type_id', array(
            'fields' => 
             array(
              0 => 'member_card_type_id',
             ),
      ));
    }
  }
}

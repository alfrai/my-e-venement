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
// from v2.2 step 2
class Version2 extends Doctrine_Migration_Base
{
  public function down()
  {
    foreach ( array('member_card' => 'name', 'member_card_price_model' => 'member_card_name') as $table => $column )
    {
      foreach ( array('','_version') as $postfix )
        $this->addColumn($table.$postfix,$column,'string','255', array('notnull' => 1));
      $this->changeColumn($table, 'member_card_type_id', NULL, NULL, array('notnull' => 0));
    }
  }
  public function up()
  {
    foreach ( array('member_card' => 'name', 'member_card_price_model' => 'member_card_name') as $table => $column )
    {
      foreach ( array('','_version') as $postfix )
        $this->removeColumn($table.$postfix,$column);
      $this->changeColumn($table, 'member_card_type_id', NULL, NULL, array('notnull' => 1));
    }
  }
}

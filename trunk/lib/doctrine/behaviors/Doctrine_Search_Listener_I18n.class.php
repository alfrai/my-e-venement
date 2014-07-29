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
class Doctrine_Search_Listener_I18n extends Doctrine_Search_Listener
{
  public function postInsert(Doctrine_Event $event)
  {
    $record = $event->getInvoker();
    
    // I18N
    $data = $record->toArray();
    $columns = $record->getTable()->getColumns();
    if ( $record->getTable()->hasTemplate('I18n') )
    foreach ( $record->Translation as $translation )
    foreach ( $translation as $name => $value )
    if ( !isset($columns[$name]) )
    {
      if ( !isset($data[$name]) )
        $data[$name] = '';
      $data[$name] .= ' '.$value;
    }
    
    $this->_search->updateIndex($data);
  }
  public function postUpdate(Doctrine_Event $event)
  {
    $this->postInsert($event);
  }
}

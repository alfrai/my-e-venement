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
class Doctrine_AuditLog_Listener_I18n extends Doctrine_AuditLog_Listener
{
  public function preUpdate(Doctrine_Event $event)
  {
    if ($this->_auditLog->getOption('auditLog'))
    {
      $class  = $this->_auditLog->getOption('className');
      $record = $event->getInvoker();

      $version = $this->_auditLog->getOption('version');
      $name = $version['alias'] === null ? $version['name'] : $version['alias'];

      $v = $this->_getNextVersion($record) - 1;

      // I18N
      if ( $record->getTable()->hasTemplate('I18n') )
      {
        $i18n = $record->Translation->getTable()->getColumns();
        unset($i18n['id']);
        
        foreach ( $record->Translation as $key => $translation )
        if ( $translation->isModified() )
        {
          $v++; $record->$name = $v; // careful, cannot do a "= ++$v", it bugs
          $translation->save();
          
          $version = new $class();
          $version->merge($record->toArray(), false);
          foreach ( $i18n as $column => $type )
            $version->$column = $translation->$column;
          $version->save();
        }
      }
      
      // CLASSIC
      else
      {
        $record->set($name, $v+1);
        
        $version = new $class();
        $version->merge($record->toArray(), false);
        $version->save();
      }
    }
  }

  public function postInsert(Doctrine_Event $event) 
  {
    if ($this->_auditLog->getOption('auditLog'))
    {
      $this->preUpdate($event);
    }
  }
}

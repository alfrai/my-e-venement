<?php
class Doctrine_AuditLog_Listener_I18N extends Doctrine_AuditLog_Listener
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
      try
      {
        $i18n = $record->Translation->getTable()->getColumns();
        unset($i18n['id']);
        
        foreach ( $record->Translation as $key => $translation )
        if ( $translation->isModified() )
        {
          $record->set($name, ++$v);
          $translation->save();
          
          $version = new $class();
          $version->merge($record->toArray(), false);
          foreach ( $i18n as $column => $type )
            $version->$column = $translation->$column;
          $version->save();
        }
      }
      // CLASSIC
      catch ( sfException $e )
      {
        throw new Doctrine_Exception($e);
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

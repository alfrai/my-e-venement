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
class ZooCreatorSyncTask extends sfBaseTask{

  protected $weirds = array();
  protected $collections = array();

  protected function configure() {
    $this->addOptions(array(
      new sfCommandOption('sync', null, sfCommandOption::PARAMETER_OPTIONAL, 'The sync direction (both by default or zoo2e or e2zoo, test for tests)'),
      new sfCommandOption('model', null, sfCommandOption::PARAMETER_REQUIRED, 'The objects to be sync\'ed (both by default or contact or organism)', 'both'),
      new sfCommandOption('no-del', null, sfCommandOption::PARAMETER_NONE, 'Do not try to delete data'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Force complete upload to the Zoo Creator repository (use with precaution, can take a loooong time)'),
      new sfCommandOption('nb', null, sfCommandOption::PARAMETER_REQUIRED, 'The number of contacts you want to synchronize (mainly for tests purposes, 0 = no limit)', '0'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'task'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Display debug informations'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'zoocreator-sync';
    $this->briefDescription = "Synchronize your e-venement's contacts & organisms with your distant Zoo Creator plateform";
    $this->detailedDescription = <<<EOF
      The [sc:zoocreator-sync|INFO] synchronizes your e-venement's contacts & organisms with a distant Zoo Creator plateform:
      [./symfony e-venement:zoocreator-sync --env=dev --sync=e2zoo --application=rp|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    require(dirname(__FILE__).'/../vendor/httpful.phar');
    
    // prerequiresites
    sfApplicationConfiguration::getActive()->loadHelpers(array('MultiByte'));
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance($this->configuration,$options['env']);
    Doctrine_Manager::connection()->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true );
    
    if ( !sfConfig::get('app_zoocreator_sync_auth', '')
      || !sfConfig::get('app_zoocreator_sync_appname','')
      || !sfConfig::get('app_zoocreator_sync_url','')
    )
      throw new sfCommandException(printf('The %s application is not configured for Zoo Creator features', $options['application']));
    
    $this->request = new ZooRequest(
      sfConfig::get('app_zoocreator_sync_url'),
      sfConfig::get('app_zoocreator_sync_auth'),
      sfConfig::get('app_zoocreator_sync_appname'),
      $this
    );
    
    // if no sync selected, test the connection and list the available views
    if ( !in_array($options['sync'], array('test', 'both', 'e2zoo', 'zoo2e')) )
    {
      $response = $this->request
        ->go(array('module' => 'formsandviews'))
        ->send($this);
      $r = json_decode($response->body);
      
      // ERROR
      if ( isset($r->errorlist) && count($r->errorlist) > 0 )
      {
        foreach ( $r->errorlist as $error )
          $this->logSection('Error', $error->error[1].' ('.$error->error[0].')', null, 'ERROR');
      }
      // SUCCESS
      else
      foreach ( array('form', 'view') as $type )
      {
        $this->logSection('Success', 'Here is the list of available '.$type.'s:');
        $arr = array();
        if ( isset($r->{'application-name'}[1]->{$type.'List'}) )
        foreach ( $r->{'application-name'}[1]->{$type.'List'} as $key => $form )
        foreach ( array('formlinkname', 'componentname') as $buf )
        if ( $key !== 0 && isset($form->$buf) && !in_array($form->$buf, $arr) )
        {
          $arr[] = $form->$buf;
          $this->logSection('   '.ucfirst($buf), $form->$buf);
        }
      }
      return;
    }
    
    // SHOW THE MATCHES BETWEEN LOCAL AND DISTANT MODEL
    if ( $options['sync'] == 'test' )
    foreach ( sfConfig::get('app_zoocreator_sync_matches') as $model => $distant )
    {
      $this->logSection('Fields for', $model);
      
      $response = $this->request->go(array('module' => $distant['form'], 'name' => 'fields'))->send();
      $r = json_decode($response->body);
      $fields = $r->{'application-name'}[1]->{'form-name'}[1]->Fields;
      foreach ( $fields as $field )
        $this->logSection($field->FieldName, $field->DisplayName);
    }
    
    if ( in_array($options['sync'], array('both', 'e2zoo')) )
      $this->e2zoo($options);
    if ( in_array($options['sync'], array('both', 'zoo2e')) )
      $this->zoo2e($options);
  }
  
  /**
   * function zoo2e imports Zoo Creator service's data into e-venement
   *
   * @param $option, the task options from the execute() function
   *
   **/
  protected function zoo2e(array $options)
  {
    $this->collections = array(); // for buffering
    
    foreach ( sfConfig::get('app_zoocreator_sync_matches') as $model => $distant )
    {
      $this->logSection('Sync',$model);
      $response = $this->request->go(array('module' => 'view', 'action' => $distant['view']))
        ->send();
      $cpt = 0;
      foreach ( $response->body->{$distant['form']} as $object )
      {
        if ( !isset($object->{$distant['uid']['distant']}) )
        {
          $this->logSection('Error', 'The current object does not have a UID', NULL, 'ERROR');
          continue;
        }
        
        $local = Doctrine::getTable($model)->findOneByVcardUid($object->{$distant['uid']['distant']});
        if ( !$local )
          $local = new $model;
        
        foreach ( $distant['fields'] as $field => $dfield )
        if ( is_array($dfield) && isset($dfield['name']) && isset($dfield['dist']) )
        foreach ( $dfield['dist'] as $name )
        {
          // CASE OF COMPLEXE FOREIGN RELATIONS
          if ( isset($object->$name) && trim($object->$name) )
          {
            // defining the value(s) to add into the current object
            $val = NULL;
            if (!( $val = $this->zoo2e_func($object, $name) ))
            if (!( $val = $this->zoo2e_array($object, $name) ))
              $val = trim($object->$name);
            
            $this->zoo2e_associate($local, $field, $dfield['name'], $val, isset($dfield['local']) ? $dfield['local'] : array());
          }
        }
        else
        {
          // NORMAL CASES
          if ( isset($object->$dfield) && trim($object->$dfield) )
          {
            $val = NULL;
            if (! ($val = $this->zoo2e_func($object, $dfield) ));
              $val = trim($object->$dfield);
            
            $local->$field = $val;
          }
        }
        
        // saving the new object locally
        try
        {
          $info = $local->isNew() ? 'created' : $local->isModified() ? 'modified' : 'unmodified';
          
          $local->save();
          $this->logSection($model.' '.$info, $local->vcard_uid.': '.$local);
          
          $cpt++;
          
          if ( $cpt%10 == 0 )
          {
            $before = memory_get_usage();
            $dummy = clone $this;
            $after = memory_get_usage();
            $size = $after - $before;
            unset($before, $after, $dummy);
            $this->logSection('Memory', round(memory_get_usage()/1024/1024).' Mo / '.$size);
          }
        }
        catch ( Doctrine_Connection_Exception $e ) // ERROR
        {
          $this->logSection($model.' (SQL) '.$local->vcard_uid, (string)$local, null, 'ERROR');
          $this->logSection($e->getMessage());
          unset($e);
        }
        
        $local->free(); // freeing memory
        unset($local);
        unset($object);
      }
      
      $this->logSection('Sync', $model.'s: '.$cpt);
    }
  }
  
  protected function zoo2e_func($zoo_object, $field)
  {
    if ( !preg_match('/^__[\w_]+\(.*\)$/', $field) )
      return false;
    
    $regexp = '/^__([\w_]+)\(\[\[([\w_]+)\]\](,.*)*\)$/';
    $args = array($zoo_object[preg_replace($regexp, '$2', $field)]);
    $func = preg_replace($regexp, '$1', $field);
    $args = array_merge($args, explode(',', substr(preg_replace($regexp, '$3', $field), 1)));
    
    return call_user_func_array($func, $args);
  }
  protected function zoo2e_array($zoo_object, $field)
  {
    // PRECONDITIONS
    if (!( substr($zoo_object->$field,0,1) == '[' && substr($zoo_object->$field,-1) == ']' ))
      return false;
    
    // CASE OF ARRAYS IN DISTANT OBJECT
    return explode(',',substr($zoo_object->$field,1,-1));
  }
  // associates the local object's collections w/ the given data
  protected function zoo2e_associate(Doctrine_Record $ev_object, $collection, $name, $val, $local = array())
  {
    if ( !is_array($val) )
      $val = array($val);
    
    foreach ( $val as $i => $item )
      $val[$i] = trim($item);
    
    if ( !isset($this->collections[$collection]) )
      $this->collections[$collection] = array();
    
    foreach ( $val as $item )
    {
      // buffering
      if ( !isset($this->collections[$collection][$item]) )
      {
        if ( $ev_object->{$collection} instanceof Doctrine_Collection )
        {
          $m = get_class($ev_object->{$collection}[0]);
          if ( $ev_object->{$collection}[0]->isNew() )
            unset($ev_object->{$collection}[0]);
        }
        else
        {
          $m = get_class($ev_object->{$collection});
          if ( $ev_object->{$collection}->isNew() )
            unset($ev_object->{$collection});
        }
        
        $q = Doctrine_Query::create()->from($m.' m')
          ->andWhere('m.'.$name.' = ?', $item)
          ->select('m.*');
        $elt = $q->limit(1)->fetchOne();
        $q->free();
        
        if ( !$elt )
        {
          $elt = new $m;
          $elt->$name = $item;
          foreach ( $local as $k => $v )
            $elt->$k = $v;
          $elt->save();
          $this->logSection($m, $item.' created.');
        }
        $this->collections[$collection][$item] = $elt;
      }
      
      // associating the subobject from the collection to the local object
      if ( $ev_object->{$collection} instanceof Doctrine_Collection )
        $ev_object->{$collection}[] = $this->collections[$collection][$item];
      else
        $ev_object->{$collection} = $this->collections[$collection][$item];
    }
    
    return $val;
  }

  /**
   * function e2zoo exports e-venement's data into the Zoo Creator service
   *
   * @param $option, the task options from the execute() function
   *
   **/
  protected function e2zoo(array $option)
  {
    $this->logSection('Not Implemented', 'This feature is not yet available.', NULL, 'ERROR');
  }
}

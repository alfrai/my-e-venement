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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
    
    $q = $this->buildQuery();
    
    // security stuff
    $token = sfConfig::get('app_synchronization_security_token', array());
    if ( !is_array($token) ) $token = array($token);
    
    if ( !$this->getUser()->isAuthenticated() )
    {
      if ( !isset($token[$request->getParameter('token')]) )
        throw new liSecurityException('You do not have the permission to use this feature');
      
      $this->getUser()->forceContact();
      $q->removeDqlQueryPart('where')
        ->leftJoin('me.Users u')
        ->andWhere('(TRUE')
        ->andWhere('me.id IS NULL')
        ->orWhere('u.username = ?',$token[$request->getParameter('token')])
        ->andWhere('TRUE)');
    }
    elseif ( !$this->getUser()->hasCredential('event-event-calendar') )
      $this->redirect('calendar/index');
    
    // filename
    $this->caldir   = sfConfig::get('sf_module_cache_dir').'/calendars/';
    $this->calfile = intval($eid = $request->getParameter('id')) > 0
      ? Doctrine::getTable('Event')->find(intval($eid))->slug
      : 'all';
    $this->calfile .= '-';
    $this->calfile .= $this->getUser()->isAuthenticated() ? $this->getUser()->getGuardUSer()->username : $token[$request->getParameter('token')];
    $this->calfile .= '.ics';
    
    $v = new vcalendar();
    $v->setConfig(array(
      'directory' => $this->caldir,
      'filename'  => $this->calfile,
    ));
    
    $updated = Doctrine_Query::create()->copy($q)
      ->select('max(updated_at) AS last_updated_at')
      ->fetchArray();
    
    if ( file_exists($this->caldir.$this->calfile)
      && strtotime($updated['last_updated_at']) <= filemtime($this->caldir.$this->calfile)
      && !$request->hasParameter('no-cache') )
    {
      $v->parse();
    }
    else
    {
      $events = $q->execute();
      foreach ( $events as $event )
      foreach ( $event->Manifestations as $manif )
      {
        $e = &$v->newComponent( 'vevent' );
        $e->setProperty('categories', $manif->Event->EventCategory );
        $e->setProperty('last-modified', date('YmdTHis',strtotime($manif->updated_at)) );
        
        $time = strtotime($manif->happens_at);
        $start = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time));
        $e->setProperty('dtstart', $start);
        
        $time = strtotime($manif->ends_at);
        $stop = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time));
        $e->setProperty('dtend', $stop );
        
        $e->setProperty('summary', $manif->Event );
        $e->setProperty('location', $manif->Location );
        $e->setProperty('url', url_for('manifestation/show?id='.$manif->id,true));
        
        // extra properties
        $client = sfConfig::get('project_about_client',array());
        $e->setProperty('description', $client['name']);
        $e->setProperty('transp', $request->hasParameter('transp') ? 'TRANSPARENT' : 'OPAQUE');
        $e->setProperty('status', 'CONFIRMED');
        
        // alarms
        if ( $alarms = sfConfig::get('app_synchronization_alarms', array('-1 hour')) )
        {
          if ( !is_array($alarms) )
            $alarms = array($alarms);
          
          foreach ( $alarms as $alarm )
          {
            $a = &$e->newComponent( 'valarm' );
            $a->setProperty('action', 'DISPLAY');
            
            $time = strtotime($alarm, strtotime($manif->happens_at));
            $datetime = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time));
            $a->setProperty('trigger', $datetime);
          }
        }
        
        $v->addComponent( $e );
      }
      
      if ( ! file_exists(dirname($this->caldir)) )
      {
        mkdir(dirname($this->caldir));
        chmod(dirname($this->caldir),0777);
      }
      if ( ! file_exists($this->caldir) )
      {
        mkdir($this->caldir);
        chmod($this->caldir,0777);
      }
      if ( file_exists($this->caldir.'/'.$this->calfile) )
        unlink($this->caldir.'/'.$this->calfile);
      
      $v->saveCalendar();
      chmod($this->caldir.'/'.$this->calfile,0777);
    }

    $this->calendar = $v->createCalendar();

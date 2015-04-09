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
    $only_pending = $request->hasParameter('only_pending');
    $nourl = $request->hasParameter('nourl');
    
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
    
    $v = new vcalendar;
    $v->setConfig(array(
      'directory' => $this->caldir,
      'filename'  => $this->calfile,
    ));
    
    if ( $request->getParameter('id',false) )
      $q->andWhere('e.id = ?', $request->getParameter('id'));
    $q->andWhere('reservation_confirmed = ?', !$only_pending);
    
    $updated = Doctrine_Query::create()->copy($q)
      ->select('max(m.updated_at) AS last_updated_at')
      ->fetchArray();
    
    // settings
    $alarms = sfConfig::get('app_synchronization'.($only_pending ? 'pending_alarms' : 'alarms'), array('when' => array('-1 hour'), 'what' => array('display')));
    if ( !isset($alarms['when']) )
      $alarms['when'] = array('-1 hour');
    if ( !is_array($alarms['when']) )
      $alarms['when'] = array($alarms['when']);
    if ( !isset($alarms['what']) )
      $alarms['what'] = array();
    if ( !is_array($alarms['what']) )
      $alarms['what'] = array($alarms['what']);
    foreach ( $alarms['what'] as $key => $type )
    {
      switch ( $type ) {
        case 'display':
          $alarms['what'][$key] = 'DISPLAY';
          break;
        case 'email':
          $alarms['what'][$key] = 'EMAIL';
          break;
        case 'audio':
          $alarms['what'][$key] = 'AUDIO';
          break;
      }
    }
    
    if ( file_exists($this->caldir.$this->calfile)
      && strtotime($updated[0]['last_updated_at']) <= filemtime($this->caldir.$this->calfile)
      && !$request->hasParameter('no-cache')
      && $this->getContext()->getConfiguration()->getEnvironment() != 'dev' )
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
        $start = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time),'tz'=>date('T'));
        $e->setProperty('dtstart', $start);
        
        $time = strtotime($manif->ends_at);
        $stop = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time),'tz'=>date('T'));
        $e->setProperty('dtend', $stop );
        
        $e->setProperty('summary', $manif->Event);
        if ( !$nourl )
          $e->setProperty('url', url_for('manifestation/show?id='.$manif->id,true));
        
        $location = array((string)$manif->Location);
        if ( $manif->Location->city )
        foreach ( array('address', 'postalcode', 'city', 'country') as $prop )
          $location[] = $manif->Location->$prop;
        $e->setProperty('location', implode(', ', $location));

        // extra properties
        $client = sfConfig::get('project_about_client',array());
        $e->setProperty('description', $client['name'].(!$nourl ? "\nURL: ".url_for('manifestation/show?id='.$manif->id, true) : ''));
        $e->setProperty('transp', $request->hasParameter('transp') ? 'TRANSPARENT' : 'OPAQUE');
        $e->setProperty('status', 'CONFIRMED');
        
        $orgs = array();
        if ( $manif->Organizers->count() > 0 )
        {
          $email = '';
          foreach ( $manif->Organizers as $org )
          {
            $orgs[] = (string)$org;
            if ( $org->email )
              $email = $org->email;
            elseif ( !$email )
              $email = $org->url;
          }
          $e->setProperty('organizer', $email, array('CN' => implode(', ', $orgs)));
        }
        
        // preparing email alerts
        $to = array();
        if ( in_array('EMAIL', $alarms['what']) )
        {
          foreach ( $manif->Organizers as $org )
          if ( $org->email )
            $to[] = $org->email;
          if ( $manif->contact_id && ($manif->Applicant->sf_guard_user_id || $manif->Applicant->email) )
            $to[] = $manif->Applicant->sf_guard_user_id ? $manif->Applicant->User->email_address : $manif->Applicant->email;
        }
        
        // alarms
        foreach ( $alarms['when'] as $when )
        foreach ( $alarms['what'] as $what )
        {
          if ( $what == 'EMAIL' && count($to) == 0 )
            continue;
          
          $a = &$e->newComponent( 'valarm' );
          
          if ( $what == 'EMAIL' )
          foreach ( $to as $email )
            $a->setProperty('attendee', $email);
          
          $a->setProperty('action', $what);
          
          $time = strtotime($when, strtotime($manif->happens_at)) - date('Z');
          $datetime = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time));
          $a->setProperty('trigger', $datetime);
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

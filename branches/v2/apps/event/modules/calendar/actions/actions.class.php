<?php

/**
 * calendar actions.
 *
 * @package    e-venement
 * @subpackage calendar
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class calendarActions extends sfActions
{
  public function executeShow(sfWebRequest $request)
  {
    if ( !intval($request->getParameter('id')) )
      throw new sfException('You must ask for a specific event id');
    
    $this->event = $this->getRoute()->getObject();
    //$v = new vcalendar(array('unique_id' => $this->event->id));
    
    $this->caldate = $maxdate = $mindate = NULL;
    $now = strtotime('now');
    
    foreach ( $this->event->Manifestations as $manif )
    {
      $time = strtotime($manif->happens_at);
      if ( $time < $mindate || is_null($mindate) )
        $mindate = $time;
      if ( $time > $maxdate || is_null($maxdate) )
        $maxdate = $time;
      
      /*
      $e = &$v->newComponent( 'vevent' );
      $e->setProperty( 'categories', $manif->Event->EventCategory );
      $e->setProperty( 'last-modified', date('YmdTHis',strtotime($manif->updated_at)) );
      $e->setProperty( 'dtstart',  date('Y',$time), date('m',$time), date('d',$time), date('H',$time), date('i',$time), date('s',$time) );
      $dtend = strtotime($manif->duration.'+0',0) + $time;
      $e->setProperty( 'dtend', array('year'=>date('Y',$dtend), 'month'=>date('m',$dtend), 'day'=>date('d',$dtend), 'hour'=>date('H',$dtend), 'min'=>date('i',$dtend), 'sec'=>date('s',$dtend)) );
      $e->setProperty( 'description', $manif->Event );
      $e->setProperty( 'location', $manif->Location );
    
      $v->addComponent( $e );
      */
    }
    
    $this->calnow = $mindate > $now ? $mindate : $maxdate < $now ? $maxdate : $now;
    $this->caldir = sfConfig::get('sf_module_cache_dir').'/calendars/';
    $this->calfile = $this->event->slug.'.ics';
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
    
    /*
    $v->setConfig(array(
      'directory' => $this->caldir,
      'filename'  => $this->calfile,
    ));
    $v->saveCalendar();
    chmod($this->caldir.'/'.$this->calfile,0777);
    */
  }
}


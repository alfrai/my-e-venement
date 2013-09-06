<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    e-venement
 * @subpackage event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $a = $this->pager->getQuery()->getRootAlias();
      $this->pager->getQuery()
        ->addSelect("(SELECT min(m2.happens_at) FROM manifestation m2 WHERE m2.event_id = $a.id) AS min_happens_at")
        ->addSelect("(SELECT (CASE WHEN max(m3.happens_at) IS NULL THEN false ELSE max(m3.happens_at) > now() END) FROM manifestation m3 WHERE m3.event_id = $a.id) AS now")
        ->orderby("max_date ".(sfConfig::get('app_listing_manif_date') != 'ASC' ? 'DESC' : 'ASC').", $a.name");
    }
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeShow($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeEdit($request);
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeUpdate($request);
  }
  public function executeDelete(sfWebRequest $request)
  {
    try {
      $this->securityAccessFiltering($request);
      parent::executeDelete($request);
    }
    catch ( Doctrine_Connection_Exception $e )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__("Deleting this object has been canceled because of remaining links to externals (like tickets)."));
      $this->redirect('event/show?id='.$this->getRoute()->getObject()->id);
    }
  }
  
  protected function securityAccessFiltering(sfWebRequest $request)
  {
    if ( intval($request->getParameter('id')).'' != ''.$request->getParameter('id') )
      return;
    
    if (!in_array(
          $this->getRoute()->getObject()->meta_event_id,
          array_keys($this->getUser()->getMetaEventsCredentials())
       ))
    {
      $this->getUser()->setFlash("You can't access this object, you don't have the required permissions.");
      $this->redirect('@event');
    }
  }
  
  public function executeCalendar(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
    
    $q = $this->buildQuery();
    if ( $request->getParameter('id') )
      $q->where('id = ?',$request->getParameter('id'));
    $events = $q->execute();
    
    $this->caldir   = sfConfig::get('sf_module_cache_dir').'/calendars/';
    $this->calfile  = $request->getParameter('id') ? $events[0]->slug.'.ics' : 'all.ics';
    
    $v = new vcalendar();
    $v->setConfig(array(
      'directory' => $this->caldir,
      'filename'  => $this->calfile,
    ));
    
    $updated = Doctrine_Query::create()->copy($q)
      ->select('max(updated_at) AS last_updated_at')
      ->execute();
    
    if ( file_exists($this->caldir.$this->calfile)
      && strtotime($updated[0]->last_updated_at) <= filemtime($this->caldir.$this->calfile) )
    {
      $v->parse();
    }
    else
    {
      foreach ( $events as $event )
      foreach ( $event->Manifestations as $manif )
      {
        $time = strtotime($manif->happens_at);
        
        $e = &$v->newComponent( 'vevent' );
        $e->setProperty('categories', $manif->Event->EventCategory );
        $e->setProperty('last-modified', date('YmdTHis',strtotime($manif->updated_at)) );
        $start = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time));
        $e->setProperty('dtstart', $start);
        $time = $time+strtotime($manif->duration.'+0',0);
        $stop = array('year'=>date('Y',$time),'month'=>date('m',$time),'day'=>date('d',$time),'hour'=>date('H',$time),'min'=>date('i',$time),'sec'=>date('s',$time));
        $e->setProperty('dtend', $stop );
        $e->setProperty('summary', $manif->Event );
        $e->setProperty('location', $manif->Location );
        $e->setProperty('url', url_for('manifestation/show?id='.$manif->id,true));
      
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

    $v->returnCalendar();
    return sfView::NONE;
  }
  
  public function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $q = Doctrine_Query::create()
      ->delete()
      ->from('Event')
      ->whereIn('id', $ids);
    $count = EventFormFilter::addCredentialsQueryPart($q)->execute();

    if ($count >= count($ids))
    {
      $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
      $this->getUser()->setFlash('error', 'A problem occurs when deleting the selected items.');
    }

    $this->redirect('@event');
  }
  
  public function executeUpdateIndexes(sfWebRequest $request)
  {
    $table = Doctrine_Core::getTable('Event');
    $table->batchUpdateIndex();
    
    $this->getUser()->setFlash('notice',"Events' index table has been updated.");
    
    $this->redirect('event');
  }
  
  public function executeError404(sfWebRequest $request)
  {
  }
}

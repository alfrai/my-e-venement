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
      $this->pager->getQuery()->orderby('me.name, name');
    }
  }
  
  public function executeCalendar(sfWebRequest $request)
  {
    $this->executeShow($request);
    $v = new vcalendar(array('unique_id' => $this->event->id));
    
    foreach ( $this->event->Manifestations as $manif )
    {
      $time = strtotime($manif->happens_at);
      
      $e = &$v->newComponent( 'vevent' );
      $e->setProperty( 'categories', $manif->Event->EventCategory );
      $e->setProperty( 'last-modified', date('YmdTHis',strtotime($manif->updated_at)) );
      $e->setProperty( 'dtstart',  date('Y',$time), date('m',$time), date('d',$time), date('H',$time), date('i',$time), date('s',$time) );
      $e->setProperty( 'duration', 0, $manif->duration, 0 );
      $e->setProperty( 'description', $manif->Event );
      $e->setProperty( 'location', $manif->Location );
    
      $v->addComponent( $e );
    }
    
    $this->ical = $v->createCalendar();
    $v->returnCalendar();
    return sfView::NONE;
  }

  public function executeUpdateIndexes(sfWebRequest $request)
  {
    $table = Doctrine_Core::getTable('Event');
    $table->batchUpdateIndex();
    
    $this->getUser()->setFlash('notice',"Events' index table has been updated.");
    
    $this->redirect('event');
  }
}

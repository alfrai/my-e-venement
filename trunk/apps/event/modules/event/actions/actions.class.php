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
        //->addSelect("(SELECT min(m2.happens_at) FROM manifestation m2 WHERE m2.event_id = $a.id) AS min_happens_at")
        ->addSelect("(SELECT (CASE WHEN max(m3.happens_at) IS NULL THEN false ELSE max(m3.happens_at) > now() END) FROM manifestation m3 WHERE m3.event_id = $a.id) AS now")
        ->orderby("max_date ".(sfConfig::get('app_listing_manif_date','DESC') != 'ASC' ? 'DESC' : 'ASC').", $a.name");
    }
  }
  
  public function executeOnlyFilters(sfWebRequest $request)
  {
    parent::executeIndex($request);
    $a = $this->pager->getQuery()->getRootAlias();
    $this->pager->getQuery()->select("$a.id");
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    $table = Doctrine::getTable('Event');
    
    $search = $this->sanitizeSearch($request->getParameter('s'));
    $transliterate = sfConfig::get('software_internals_transliterate',array());
    
    $this->pager->setQuery($table->search($search.'*',$this->pager->getQuery()));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
    
    $this->setTemplate('index');
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeShow($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request, true);
    parent::executeEdit($request);
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request, true);
    parent::executeUpdate($request);
  }
  public function executeDelete(sfWebRequest $request)
  {
    try {
      $this->securityAccessFiltering($request, true);
      parent::executeDelete($request);
    }
    catch ( Doctrine_Connection_Exception $e )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__("Deleting this object has been canceled because of remaining links to externals (like tickets)."));
      $this->redirect('event/show?id='.$this->getRoute()->getObject()->id);
    }
  }
  
  protected function securityAccessFiltering(sfWebRequest $request, $deep = false)
  {
    if ( intval($request->getParameter('id')).'' != ''.$request->getParameter('id') )
      return;
    
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    if ( $deep && !$this->getUser()->hasCredential('event-access-all') )
    foreach ( $this->getRoute()->getObject()->Manifestations as $manif )
    if ( $manif->contact_id !== $this->getUser()->getContactId() )
    {
      $this->getUser()->setFlash('error', __("You cannot edit an event object in which there are manifestations that do not belong to you."));
      $this->redirect('event/show?id='.$this->getRoute()->getObject()->getId());
    }
    
    if (!in_array(
          $this->getRoute()->getObject()->meta_event_id,
          array_keys($this->getUser()->getMetaEventsCredentials())
       ))
    {
      $this->getUser()->setFlash('error', "You can't access this object, you don't have the required permissions.");
      $this->redirect('@event');
    }
  }
  
  public function executeCalendar(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/calendar.php');
  }
  
  public function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $q = Doctrine_Query::create()
      ->from('Event e')
      ->whereIn('e.id', $ids)
      ->andWhere('(SELECT count(m.id) FROM Manifestation m WHERE m.event_id = id AND m.contact_id != ?) = 0', $this->getUser()->getContactId())
      ->delete();
    $count = EventFormFilter::addCredentialsQueryPart(Doctrine::getTable('Event')->createQuery('e')->whereIn('e.id', $ids)->select('e.id'))->execute()->count();
    
    if ($count >= count($ids))
    {
      $q->execute();
      $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
      $this->getUser()->setFlash('error', 'A problem occurs when deleting the selected items.');
    }

    $this->redirect('@event');
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('Event')
      ->createQuery('e')
      ->orderBy('name')
      ->limit($request->getParameter('limit'))
      ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()));
    $q = Doctrine_Core::getTable('Event')
      ->search($search.'*',$q);
    $request = $q->execute()->getData();

    $events = array();
    foreach ( $request as $event )
      $events[$event->id] = (string) $event;
    
    return $this->renderText(json_encode($events));
  }
  
  public function executeError404(sfWebRequest $request)
  {
  }
  
  public function executeAddManifestation(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->redirect('manifestation/new?event='.$this->event->slug);
  }

  public static function sanitizeSearch($search)
  {
    $nb = strlen($search);
    $charset = sfConfig::get('software_internals_charset');
    return str_replace(array('-','+',','),' ',strtolower(iconv($charset['db'],$charset['ascii'],substr($search,$nb-1,$nb) == '*' ? substr($search,0,$nb-1) : $search)));
  }
}

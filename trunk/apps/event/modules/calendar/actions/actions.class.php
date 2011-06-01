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
  public function executeIndex(sfWebRequest $request)
  {
    $q = Doctrine::getTable('Event')->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Manifestations m")
      ->select('max(m.happens_at) AS max, min(m.happens_at) AS min');
    $range = $q->fetchArray();
    $range = $range[0];
    
    $this->setNow($range);
    
    $this->setTemplate('show');
  }
  public function executeShow(sfWebRequest $request)
  {
    if ( intval($request->getParameter('id')) <= 0 )
      throw new sfError404Exception();
    
    $q = Doctrine::getTable('Event')->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Manifestations m")
      ->where('id = ?',intval($request->getParameter('id')))
      ->select('max(m.happens_at) AS max, min(m.happens_at) AS min');
    $range = $q->fetchArray();
    $range = $range[0];
    
    $this->setNow($range);
  }

  protected function setNow($range = array())
  {
    $now = strtotime('now');
    return
    $this->calnow = strtotime($range['min']) > $now
      ? strtotime($range['min'])
      : strtotime($range['max']) < $now
      ? strtotime($range['max'])
      : $now;
  }
}


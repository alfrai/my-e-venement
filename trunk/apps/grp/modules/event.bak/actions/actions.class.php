<?php

/**
 * event actions.
 *
 * @package    e-venement
 * @subpackage event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $q = Doctrine::getTable('Event')->createQuery('e')
      ->leftJoin('e.Manifestations m')
      ->andWhere('m.id IN (SELECT entry.manifestation_id FROM Entry entry)');
    $this->events = $q->execute();
  }
}

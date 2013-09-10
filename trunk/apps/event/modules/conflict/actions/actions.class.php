<?php

/**
 * conflict actions.
 *
 * @package    e-venement
 * @subpackage conflict
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class conflictActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $table = Doctrine::getTable('Manifestation');
    
    // the root raw query
    $this->conflicts = $table->getConflicts();
    
    // the real doctrine objects
    $q = $table->createQuery('m')
      ->andWhereIn('m.id',array_keys($this->conflicts))
      ->orderBy('m.happens_at');
    $this->manifestations = $q->execute();
    
    // other required empty vars
    $this->helper = true;
    $this->pager = true;
  }
}

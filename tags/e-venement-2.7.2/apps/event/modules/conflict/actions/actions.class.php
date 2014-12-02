<?php

require_once dirname(__FILE__).'/../lib/conflictGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/conflictGeneratorHelper.class.php';

/**
 * conflict actions.
 *
 * @package    e-venement
 * @subpackage conflict
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class conflictActions extends autoConflictActions
{
  public function executeEdit(sfWebRequest $request)
  {
    $this->redirect('manifestation/edit?id='.$request->getParameter('id'));
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('manifestation/show?id='.$request->getParameter('id'));
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->redirect('manifestation/new');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->executeNew($request);
  }
}

<?php

require_once dirname(__FILE__).'/../lib/workspaceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/workspaceGeneratorHelper.class.php';

/**
 * workspace actions.
 *
 * @package    e-venement
 * @subpackage workspace
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class workspaceActions extends autoWorkspaceActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->workspace = Doctrine::getTable('Workspace')->createQuery('w')
      ->leftJoin('w.Users u')
      ->leftJoin('w.Manifestations m')
      ->leftJoin('m.Event e')
      ->leftJoin('w.Prices p')
      ->andWhereIn('w.id',array_keys($this->getUser()->getWorkspacesCredentials()))
      ->andWhereIn('e.meta_event_id IS NULL OR e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()))
      ->andWhere('w.id = ?',$request->getParameter('id'))
      ->orderBy('w.name, u.username, m.happens_at, p.name')
      ->fetchOne();
    $this->forward404Unless($this->workspace);
    $this->form = $this->configuration->getForm($this->workspace);
  }
}


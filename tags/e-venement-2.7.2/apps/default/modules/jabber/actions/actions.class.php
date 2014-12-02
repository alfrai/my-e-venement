<?php

require_once dirname(__FILE__).'/../lib/jabberGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/jabberGeneratorHelper.class.php';

/**
 * jabber actions.
 *
 * @package    e-venement
 * @subpackage jabber
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class jabberActions extends autoJabberActions
{
  public function executeIndex(sfWebRequest $request)
  {
    // POWER
    if ( !$this->getUser()->hasCredential('admin-power') )
      $this->redirect('jabber/'.($this->getUser()->getJabber()->count() == 0 ? 'new' : 'edit?id='.$this->getUser()->getJabber(0)->id));
    parent::executeIndex($request);
  }
  
  public function executeNew(sfWebRequest $request)
  {
    // POWER
    if ( !$this->getUser()->hasCredential('admin-power') && $this->getUser()->getJabber()->count() > 0 )
      $this->redirect('jabber/edit?id='.$this->getUser()->getJabber(0)->id);
    parent::executeNew($request);
  }
}

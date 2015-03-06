<?php

/**
 * bp actions.
 *
 * @package    e-venement
 * @subpackage bp
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class bpActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('bp', 'show');
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->forward404Unless($id = $request->getParameter('id', false));
    $this->forward404Unless($this->pdt = Doctrine::getTable('BoughtProduct')->find($id));
    
    // if the contacts do not match
    if ( $this->pdt->Transaction->contact_id != $this->getUser()->getTransaction()->contact_id )
    {
      $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
      $this->getUser()->setFlash('error', __('Authentication failure.'));
      $this->redirect('login/index');
    }
  }
}

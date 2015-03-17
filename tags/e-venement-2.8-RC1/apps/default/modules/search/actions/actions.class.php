<?php

/**
 * search actions.
 *
 * @package    e-venement
 * @subpackage search
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class searchActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if (!( $this->search = $request->getParameter('search',false) ))
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('Try again your global search with a valid string'));
      $this->redirect('default/index');
    }
  }
}

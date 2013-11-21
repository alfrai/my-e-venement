<?php

require_once dirname(__FILE__).'/../lib/orderGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/orderGeneratorHelper.class.php';

/**
 * order actions.
 *
 * @package    e-venement
 * @subpackage order
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class orderActions extends autoOrderActions
{
  public function executeCancel(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    
    if ( intval($id = intval($request->getParameter('id'))) > 0 )
    {
      Doctrine::getTable('Order')->findOneById($id)
        ->delete();
      $this->getUser()->setFlash('notice',__('The given order has been cancelled successfully'));
    }
    else
      $this->getUser()->setFlash('error',__('Unable to find the given order for cancellation'));
    
    $this->redirect('@order');
  }
}

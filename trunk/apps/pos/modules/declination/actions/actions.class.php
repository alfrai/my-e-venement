<?php

require_once dirname(__FILE__).'/../lib/declinationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/declinationGeneratorHelper.class.php';

/**
 * declination actions.
 *
 * @package    e-venement
 * @subpackage declination
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class declinationActions extends autoDeclinationActions
{
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    if ( $pid = $request->getParameter('product-id', false) )
      $this->form->setDefault('product_id', $pid);
  }
  
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
    
    $obj = $this->getRoute()->getObject();
    $parent_id = $obj->product_id;
    $obj->delete();

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('product/edit?id='.$parent_id);
  }
  
  public function executeBackToProduct(sfWebRequest $request)
  {
    parent::executeEdit($request);
    if ( $request->hasParameter('id') )
    {
      $this->executeEdit($request);
      $this->redirect('product/edit?id='.$this->product_declination->product_id.'#sf_fieldset_declinations');
    }
    else
      $this->redirect('@product');
  }
}

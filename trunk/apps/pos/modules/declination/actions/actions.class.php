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
  
  public function executeAjax(sfWebRequest $request)
  {
    //$this->getResponse()->setContentType('application/json');
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->setContentType('text/html');
      sfConfig::set('sf_debug',true);
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('ProductDeclination')->createQuery('d')
      ->leftJoin('d.Product pdt')
      ->leftJoin('pdt.MetaEvent me')
      ->andWhereIn('me.id IS NULL OR me.id', array_keys($this->getUser()->getMetaEventsCredentials()))
      ->limit($request->getParameter('limit', $request->getParameter('max', 10)))
      //->orderBy('pt.name')
    ;
    if ( ($tid = intval($request->getParameter('except_transaction', false))).'' === ''.$request->getParameter('except_transaction', false) )
      $q->andWhere('pdt.id NOT IN (SELECT bpd.product_id FROM BoughtProduct bp LEFT JOIN bp.Declination bpd WHERE bp.transaction_id = ?)',$tid);
    
    // huge hack to look for declinations' codes AND product_index
    $pdtq = Doctrine::getTable('Product')->search($search.'*', Doctrine_Query::create()->from('Product'))->select('id');
    $q->andWhere('(TRUE')
      ->andWhere('d.code ILIKE ?', $request->getParameter('q').'%')
      ->orWhere('TRUE');
    $q = Doctrine_Core::getTable('ProductDeclination')
      ->search($search.'*',$q)
      ->orWhere("pdt.id IN ($pdtq)", $pdtq->getFlattenedParams());
    $q->andWhere('TRUE)')
      ->leftJoin('d.Translation dt')
      ->leftJoin('pdt.Translation pt')
      ->andWhere('pt.lang = dt.lang AND dt.lang = ?', $this->getUser()->getCulture())
    ;
    
    $this->declinations = array();
    foreach ( $q->execute() as $declination )
    if ( $declination->Product->isAccessibleBy($this->getUser()) )
    if ( $request->hasParameter('keep-order') )
    {
      $this->declinations[] = array(
        'id'    => $declination->id,
        'color' => NULL,
        'name'  => (string)$declination,
      );
    }
    else
      $this->declinations[$declination->id] = $request->hasParameter('with_colors')
        ? array('name' => (string)$declination, 'color' => NULL)
        : (string) $declination;
  }
}

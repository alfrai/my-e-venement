<?php

require_once dirname(__FILE__).'/../lib/paymentGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/paymentGeneratorHelper.class.php';

/**
 * payment actions.
 *
 * @package    e-venement
 * @subpackage payment
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class paymentActions extends autoPaymentActions
{
  public function executeIndex(sfWebRequest $request)
  {
    if ( $tid = intval($request->getParameter('transaction_id')) )
    {
      parent::executeIndex($request);
      $this->pager = new sfDoctrinePager('Payment',1000);
      $this->pager->setQuery(
        Doctrine::getTable('Payment')->createQuery()
          ->andWhere('transaction_id = ?',$tid)
          ->orderBy('updated_at DESC')
      );
      $this->pager->setPage(1);
      $this->pager->init();
      $this->sort = array('updated_at','DESC');
      $this->hasFilters = $this->getUser()->getAttribute('payment.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    }
    else
    {
      parent::executeIndex($request);
    }
  }
}

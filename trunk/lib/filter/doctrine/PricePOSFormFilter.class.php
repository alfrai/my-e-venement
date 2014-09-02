<?php

/**
 * PricePOS filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PricePOSFormFilter extends BasePricePOSFormFilter
{
  protected $user = NULL;
  public function configure()
  {
    $this->widgetSchema   ['price_id']
      ->setOption('query', $q = Doctrine::getTable('Price')->createQuery('p')->leftJoin('p.PricePOS pos')->leftJoin('p.Products pdt')->andWhere('pos.id IS NOT NULL OR pdt.id IS NOT NULL'))
      ->setOption('order_by', array('pdt.id IS NOT NULL, p.name', ''))
      ->setOption('multiple', true)
      ->setOption('add_empty', false)
    ;
    $this->validatorSchema['price_id']
      ->setOption('query', $q)
      ->setOption('multiple', true)
    ;
    
    if ( !sfContext::hasInstance() )
      return;
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema['price_id']->getOption('query')->leftJoin('p.Users pu')->andWhere('pu.id = ?', $this->user->getId());
  }
}

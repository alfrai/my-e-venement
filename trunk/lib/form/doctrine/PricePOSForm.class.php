<?php

/**
 * PricePOS form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PricePOSForm extends BasePricePOSForm
{
  protected $user = NULL;
  
  public function configure()
  {
    $this->widgetSchema   ['price_id']
      ->setOption('query', $q = Doctrine::getTable('Price')->createQuery('p')->leftJoin('p.PricePOS pos')->andWhere('pos.id IS NULL'))
      ->setOption('order_by', array('p.name', ''))
    ;
    $this->validatorSchema['price_id']->setOption('query', $q);
    
    if ( !sfContext::hasInstance() )
      return;
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema['price_id']->getOption('query')->leftJoin('p.Users pu')->andWhere('pu.id = ?', $this->user->getId());
  }
}

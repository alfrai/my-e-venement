<?php

/**
 * MemberCardPriceModel form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardPriceModelForm extends BaseMemberCardPriceModelForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema   ['price_id']->setOption('order_by',array('name',''));
    $this->widgetSchema   ['price_id']->setOption('query',$q = Doctrine::getTable('Price')->createQuery('p')->andWhere('p.member_card_linked = true'));
    $this->validatorSchema['price_id']->setOption('query',$q);
  }
}

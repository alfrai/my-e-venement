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
    
    $this->widgetSchema   ['member_card_type_id']->setOption('order_by',array('name',''));
    
    $this->widgetSchema   ['price_id']->setOption('order_by',array('name',''));
    $this->widgetSchema   ['price_id']->setOption('query',$q = Doctrine::getTable('Price')->createQuery('p')->andWhere('p.member_card_linked = true'));
    $this->validatorSchema['price_id']->setOption('query',$q);
    
    $this->widgetSchema   ['event_id']->setOption('order_by',array('translation.name',''));
    $this->widgetSchema   ['event_id']->setOption('query',$q = Doctrine::getTable('Event')->createQuery('e')->andWhereIn('e.meta_event_id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())));
    $this->validatorSchema['event_id']->setOption('query',$q);
  }
}

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
    
    $this->widgetSchema   ['member_card_type_id']
        ->setOption('order_by',array('name',''))
        ->setOption('query', Doctrine::getTable($this->widgetSchema['member_card_type_id']->getOption('model'))->createQuery('mcpm'))
    ;
    
    $this->widgetSchema   ['price_id']->setOption('order_by',array('name',''));
    $this->widgetSchema   ['price_id']->setOption('query',$q = Doctrine::getTable('Price')->createQuery('p')->andWhere('p.member_card_linked = true'));
    $this->validatorSchema['price_id']->setOption('query',$q);
    
    $this->widgetSchema   ['event_id']->setOption('order_by',array('translation.name',''));
    $this->widgetSchema   ['event_id']->setOption('query',$q = Doctrine::getTable('Event')->createQuery('e')->andWhereIn('e.meta_event_id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())));
    $this->validatorSchema['event_id']->setOption('query',$q);
    
    $this->widgetSchema['quantity']
      ->setOption('type', 'number')
      ->setAttribute('min', -1)
      ->setAttribute('max', 50)
      ->setDefault(1)
    ;
    
    if ( $this->object->isNew() )
    {
      $this->widgetSchema   ['event_id']->setOption('multiple', true)
        ->setOption('add_empty', false);
      $this->validatorSchema['event_id']->setOption('multiple', true);
    }
  }
  
  public function doSave($con = null)
  {
    if (null === $con)
      $con = $this->getConnection();
    
    if ( !is_array($this->values['event_id']) )
      $this->values['event_id'] = array($this->values['event_id']);
    foreach ( $this->values['event_id'] as $event_id )
    {
      $last = $this->object;
      $this->values['event_id'] = $event_id;
      $this->updateObject();
      $this->getObject()->save($con);
    
      // embedded forms
      $this->saveEmbeddedForms($con);
      
      $class = $this->getModelName();
      $this->object = new $class;
    }
    $this->object = $last;
  }
}

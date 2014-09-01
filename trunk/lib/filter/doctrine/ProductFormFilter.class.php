<?php

/**
 * Product filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductFormFilter extends BaseProductFormFilter
{
  protected $user = NULL;
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema   ['prices_list']
      ->setOption('multiple', true)->setOption('order_by', array('name',''))
      ->setOption('query', $q = Doctrine::getTable('Price')->createQuery('p')->leftJoin('p.PricePOS pos')->andWhere('pos.id IS NOT NULL'))
    ;
    $this->validatorSchema['prices_list']->setOption('query', $q);
    
    $this->widgetSchema   ['meta_event_id']
      ->setOption('multiple', true)
      ->setOption('order_by', array('name',''))
      ->setOption('add_empty', false);
    $this->validatorSchema['meta_event_id']->setOption('multiple', true);
    
    $this->widgetSchema   ['product_category_id']->setOption('order_by', array('pct.name', ''));
    
    if ( !sfContext::hasInstance() )
      return;
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema   ['prices_list']->getOption('query')->leftJoin('p.Users u')->andWhere('u.id = ?', $this->user->getId());
    
    $this->widgetSchema   ['meta_event_id']->setOption('query', $q = Doctrine::getTable('MetaEvent')->createQuery('me')->andWhereIn('me.id', array_keys($this->user->getMetaEventsCredentials())));
    $this->validatorSchema['meta_event_id']->setOption('query', $q);
  }
  
  public function getFields()
  {
    return parent::getFields() + array(
      'name' => 'Name',
    );
  }
  
  public function addNameColumnQuery($q, $field, $value)
  {
    if ( !$value['text'] )
      return $q;
    
    $q->andWhere('pt.name ILIKE ?', $value['text'].'%');
    if ( $this->user )
      $q->andWhere('pt.lang = ?', $this->user->getCulture());
    
    return $q;
  }
}

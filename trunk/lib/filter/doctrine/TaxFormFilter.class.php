<?php

/**
 * Tax filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TaxFormFilter extends BaseTaxFormFilter
{
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema   ['users_list']
      ->setOption('query', Doctrine::getTable('SfGuardUser')->createQuery('u')
        ->andWhere('u.is_active = ?', true)
      )
      ->setOption('order_by', array('u.username',''))
    ;
    $this->widgetSchema   ['prices_list']
      ->setOption('order_by', array('name',''));
    $this->widgetSchema   ['manifestations_list']
      ->setOption('order_by', array('et.name',''));
  }
}

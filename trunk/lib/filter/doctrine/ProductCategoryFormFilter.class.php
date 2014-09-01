<?php

/**
 * ProductCategory filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductCategoryFormFilter extends BaseProductCategoryFormFilter
{
  protected $user = NULL;
  
  public function configure()
  {
    if ( sfContext::hasInstance() )
      $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema   ['name']  = new sfWidgetFormInput;
    $this->validatorSchema['name']  = new sfValidatorString(array('required' => false));
  }
  
  public function getFields()
  {
    return parent::getFields() + array(
      'name' => 'Name',
    );
  }
  
  public function addNameColumnQuery($q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $q->andWhere('pct.name ILIKE ?', $value.'%');
    if ( $this->user )
      $q->andWhere('pct.lang = ?', $this->user->getCulture());
    
    return $q;
  }
}

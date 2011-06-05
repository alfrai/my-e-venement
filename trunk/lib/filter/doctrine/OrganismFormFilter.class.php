<?php

/**
 * Organism filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OrganismFormFilter extends BaseOrganismFormFilter
{
  /**
   * @see AddressableFormFilter
   */
  public function configure()
  {
    $this->widgetSchema['organism_category_id']->setOption('order_by',array('name',''));
    parent::configure();
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['postalcode']           = 'Postalcode';
    return $fields;
  }
  public function addPostalcodeColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( intval($value['text']) > 0 )
      $q->addWhere("$c.postalcode LIKE ?",intval($value['text']).'%');
    
    return $q;
  }
}

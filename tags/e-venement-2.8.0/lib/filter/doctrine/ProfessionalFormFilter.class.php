<?php

/**
 * Professional filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProfessionalFormFilter extends BaseProfessionalFormFilter
{
  public function configure()
  {
    $this->widgetSchema['professional_type_id']->setOption('order_by',array('name',''));
    
    $this->widgetSchema['contact_name'] = new sfWidgetFormInput(array(
    ));
    $this->validatorSchema['contact_name'] = new sfValidatorString(array(
      'required' => false,
    ));
    
    $this->widgetSchema['organism_name'] = new sfWidgetFormInput(array(
    ));
    $this->validatorSchema['organism_name'] = new sfValidatorString(array(
      'required' => false,
    ));
  }
  
  public function getFields()
  {
    return array_merge(
      array(
        'contact_name' => 'Contact name',
        'organism_name' => 'Organism name',
      ),
      parent::getFields()
    );
  }
  
  public function addContactNameColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);
    
    if ( $value )
    {
      $a = $query->getRootAlias();
      $query->addWhere('LOWER(c.name) LIKE ?',strtolower($value).'%');
    }
    
    return $query;
  }
  
  public function addOrganismNameColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);
    
    if ( $value )
    {
      $a = $query->getRootAlias();
      $query->addWhere('LOWER(o.name) LIKE ?',strtolower($value).'%');
    }
    
    return $query;
  }
}

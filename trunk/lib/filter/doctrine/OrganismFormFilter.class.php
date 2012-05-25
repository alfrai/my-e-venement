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
    $this->widgetSchema['organism_category_id']->setOption('multiple',true);
    $this->widgetSchema['organism_category_id']->setOption('add_empty',false);
    $this->validatorSchema['organism_category_id']->setOption('multiple',true);
    
    $this->widgetSchema['contacts_groups'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Group',
      'multiple' => true,
    ));
    $this->validatorSchema['contacts_groups'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Group',
      'required' => false,
      'multiple' => true,
    ));
    
    parent::configure();
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['postalcode']           = 'Postalcode';
    $fields['contacts_groups']      = 'ContactsGroups';
    return $fields;
  }
  public function addContactsGroupsColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( intval($value['text']) > 0 )
    {
      $q->leftJoin('c.Groups gc')
        ->leftJoin('p.Groups gp')
        ->andWhere('(TRUE')
        ->andWhereIn("gp.id",intval($value['text']))
        ->orWhereIn("gc.id",intval($value['text']))
        ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addPostalcodeColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( intval($value['text']) > 0 )
      $q->addWhere("$c.postalcode LIKE ?",intval($value['text']).'%');
    
    return $q;
  }
}

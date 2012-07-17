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
      'order_by' => array('name',''),
    ));
    $this->validatorSchema['contacts_groups'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Group',
      'required' => false,
      'multiple' => true,
    ));
    
    $this->widgetSchema['professional_meta_event_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'multiple' => true,
    ));
    $this->validatorSchema['professional_meta_event_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MetaEvent',
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
    $fields['professional_meta_event_id'] = 'ProfessionalMetaEventId';
    return $fields;
  }
  public function addContactsGroupsColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( is_array($value) )
    {
      $q->leftJoin('c.Groups gc')
        ->leftJoin('p.Groups gp')
        ->andWhere('(TRUE')
        ->andWhereIn("gp.id",$value)
        ->orWhereIn("gc.id",$value)
        ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addProfessionalMetaEventIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( is_array($value) )
    {
      $q->leftJoin('p.Transactions tr')
        ->leftJoin('tr.id IS NOT NULL');
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

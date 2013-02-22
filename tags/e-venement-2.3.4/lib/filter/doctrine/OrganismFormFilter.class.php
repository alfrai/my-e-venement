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
    $this->widgetSchema['groups_list']->setOption(
      'order_by',
      array('u.id IS NULL DESC, u.username, name','')
    );
    
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
    $this->widgetSchema   ['not_groups_list'] = $this->widgetSchema   ['groups_list'];
    $this->validatorSchema['not_groups_list'] = $this->validatorSchema['groups_list'];
    
    $this->widgetSchema['professional_meta_event_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'order_by' => array('name',''),
      'multiple' => true,
    ));
    $this->validatorSchema['professional_meta_event_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MetaEvent',
      'required' => false,
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['region_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'GeoFrRegion',
      'order_by' => array('name',''),
      'add_empty' => true,
    ));
    $this->validatorSchema['region_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'GeoFrRegion',
      'required' => false,
    ));
    
    parent::configure();
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['postalcode']           = 'Postalcode';
    $fields['contacts_groups']      = 'ContactsGroups';
    $fields['professional_meta_event_id'] = 'ProfessionalMetaEventId';
    $fields['not_groups_list']      = 'NotGroupsList';
    $fields['region']               = 'RegionId';

    return $fields;
  }

  public function addRegionIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( intval($value) > 0 )
      $q->andWhere("SUBSTRING($a.postalcode,1,2) IN (SELECT REGEXP_REPLACE(dpt.num, '[a-zA-Z]', '0') FROM GeoFrDepartment dpt LEFT JOIN dpt.Region reg WHERE reg.id = ?)",$value)
        ->andWhere("LOWER($a.country) = ? OR TRIM($a.country) = ? OR $a.country IS NULL",array('france',''));
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
        ->andWhere('tr.id IS NOT NULL')
        ->andWhere('p.id IS NOT NULL');
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
  
  public function addNotGroupsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    {
      $q1 = new Doctrine_Query();
      $q1->select('tmp1.organism_id')
        ->from('GroupOrganism tmp1')
        ->andWhereIn('tmp1.group_id',$value);
      
      $q->andWhere("$a.id NOT IN (".$q1.")",$value); // hack for inserting $value
    }
    
    return $q;
  }
}

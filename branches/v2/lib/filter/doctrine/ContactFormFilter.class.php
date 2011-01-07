<?php

/**
 * Contact filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactFormFilter extends BaseContactFormFilter
{
  /**
   * @see AddressableFormFilter
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    $this->widgetSchema['groups_list']->setOption('order_by',array('u.id IS NULL DESC, u.username, name',''));
    
    // has postal address ?
    $this->widgetSchema   ['has_address'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_address'] = $this->validatorSchema['npai'];
    
    // has email address ?
    $this->widgetSchema   ['has_email'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_email'] = $this->validatorSchema['npai'];
    
    // organism
    $this->widgetSchema   ['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorBoolean(array('required' => false));
    
    // organism category
    $this->widgetSchema   ['organism_category_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'OrganismCategory',
      'add_empty' => true,
    ));
    $this->validatorSchema['organism_category_id'] = new sfValidatorInteger(array('required' => false));
    
    // professional type
    $this->widgetSchema   ['professional_type_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'ProfessionalType',
      'add_empty' => true,
    ));
    $this->validatorSchema['professional_type_id'] = new sfValidatorInteger(array('required' => false));
    
    $years = sfContext::getInstance()->getConfiguration()->yob;
    $this->widgetSchema   ['YOB'] = new sfWidgetFormFilterDate(array(
      'from_date' => new sfWidgetFormDate(array(
        'format' => '%year% %month% %day%',
        'years'  => $years,
        //'empty_values' => array('year' => '', 'month' => '01', 'day' => '01',),
      )),
      'to_date'   => new sfWidgetFormDate(array(
        'format' => '%year% %month% %day%',
        'years'  => $years,
        //'empty_values' => array('year' => '', 'month' => '01', 'day' => '01',),
      )),
      'with_empty'=> false,
      'template'  => '<span class="from_year">'.__('From %from_date%').'</span> <span class="to_year">'.__('to %to_date%').'</span>',
    ));
    $this->validatorSchema['YOB'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false,)),
      'to_date'   => new sfValidatorDate(array('required' => false,)),
    ));
    
    /*
    // organising in fieldsets
    foreach ( array(
      'personal' => array(
        'firstname',
        'name',
        'postalcode',
        'city',
        'country',
      ),
      'additional' => array(
        'description',
        'YOB',
        'npai',
        'has_address',
        'has_email',
      ),
      'relations' => array(
        'groups_list',
        'organism_id',
        'organism_category_id',
        'professional_type_id',
      )) as $fieldset => $fields )
    {
      $this->widgetSchema   [$fieldset]    = new sfWidgetFormSchema();
      //$this->validatorSchema[$fieldset]    = new sfValidatorPass();
      foreach ( $fields as $name )
      {
        $this->widgetSchema   [$fieldset][$name] = $this->widgetSchema   [$name];
        $this->validatorSchema   [$fieldset][$name] = $this->validatorSchema   [$name];
      }
    }
    */
    
    parent::configure();
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['YOB']                  = 'YOB';
    $fields['organism_id']          = 'OrganismId';
    $fields['organism_category_id'] = 'OrganismCategoryId';
    $fields['professional_type_id'] = 'OrganismCategoryId';
    $fields['has_email']            = 'HasEmail';
    $fields['has_address']          = 'HasAddress';
    
    return $fields;
  }
  
  public function addHasAddressColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.postalcode IS NOT NULL OR $a.city IS NOT NULL");
    else
      return $q->addWhere("$a.postalcode IS     NULL OR $a.city IS     NULL");
  }
  public function addHasEmailColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.email IS NOT NULL");
    else
      return $q->addWhere("$a.email IS     NULL");
  }
  public function addProfessionalTypeIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
      $q->addWhere("$a.Professionals.professional_type_id = ?",$value);
    return $q;
  }
  public function addOrganismIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
      $q->addWhere("$a.Professionals.organism_id = ?",$value);
    return $q;
  }
  public function addOrganismCategoryIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
      $q->addWhere("$a.Professionals.Organism.organism_category_id = ?",$value);
    return $q;
  }
  public function addYOBColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value['from'] )
      $q->addWhere('y.year >= ?',date('Y',strtotime($value['from'])));
    if ( $value['to'] )
      $q->addWhere('y.year <= ?',date('Y',strtotime($value['to'])));
    
    return $q;
  }
}

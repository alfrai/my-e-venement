<?php

/**
 * Professional filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseProfessionalFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'                 => new sfWidgetFormFilterInput(),
      'organism_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Organism'), 'add_empty' => true)),
      'contact_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Contact'), 'add_empty' => true)),
      'professional_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProfessionalType'), 'add_empty' => true)),
      'contact_number'       => new sfWidgetFormFilterInput(),
      'contact_email'        => new sfWidgetFormFilterInput(),
      'department'           => new sfWidgetFormFilterInput(),
      'description'          => new sfWidgetFormFilterInput(),
      'created_at'           => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'           => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'groups_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Group')),
    ));

    $this->setValidators(array(
      'name'                 => new sfValidatorPass(array('required' => false)),
      'organism_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Organism'), 'column' => 'id')),
      'contact_id'           => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Contact'), 'column' => 'id')),
      'professional_type_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ProfessionalType'), 'column' => 'id')),
      'contact_number'       => new sfValidatorPass(array('required' => false)),
      'contact_email'        => new sfValidatorPass(array('required' => false)),
      'department'           => new sfValidatorPass(array('required' => false)),
      'description'          => new sfValidatorPass(array('required' => false)),
      'created_at'           => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'           => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'groups_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Group', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('professional_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.GroupProfessional GroupProfessional')
      ->andWhereIn('GroupProfessional.group_id', $values)
    ;
  }

  public function getModelName()
  {
    return 'Professional';
  }

  public function getFields()
  {
    return array(
      'id'                   => 'Number',
      'name'                 => 'Text',
      'organism_id'          => 'ForeignKey',
      'contact_id'           => 'ForeignKey',
      'professional_type_id' => 'ForeignKey',
      'contact_number'       => 'Text',
      'contact_email'        => 'Text',
      'department'           => 'Text',
      'description'          => 'Text',
      'created_at'           => 'Date',
      'updated_at'           => 'Date',
      'groups_list'          => 'ManyKey',
    );
  }
}

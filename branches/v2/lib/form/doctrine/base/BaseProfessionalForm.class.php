<?php

/**
 * Professional form base class.
 *
 * @method Professional getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseProfessionalForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'name'                 => new sfWidgetFormInputText(),
      'organism_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Organism'), 'add_empty' => false)),
      'contact_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Contact'), 'add_empty' => false)),
      'professional_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProfessionalType'), 'add_empty' => true)),
      'contact_number'       => new sfWidgetFormInputText(),
      'contact_email'        => new sfWidgetFormInputText(),
      'department'           => new sfWidgetFormInputText(),
      'description'          => new sfWidgetFormTextarea(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
      'groups_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Group')),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'                 => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'organism_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Organism'))),
      'contact_id'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Contact'))),
      'professional_type_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProfessionalType'), 'required' => false)),
      'contact_number'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'contact_email'        => new sfValidatorEmail(array('max_length' => 255, 'required' => false)),
      'department'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'          => new sfValidatorString(array('required' => false)),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
      'groups_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Group', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('professional[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Professional';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveGroupsList($con);

    parent::doSave($con);
  }

  public function saveGroupsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Groups->getPrimaryKeys();
    $values = $this->getValue('groups_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Groups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Groups', array_values($link));
    }
  }

}

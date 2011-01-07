<?php

/**
 * Group form base class.
 *
 * @method Group getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'name'               => new sfWidgetFormInputText(),
      'sf_guard_user_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('User'), 'add_empty' => true)),
      'description'        => new sfWidgetFormTextarea(),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
      'slug'               => new sfWidgetFormInputText(),
      'contacts_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Contact')),
      'professionals_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Professional')),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'               => new sfValidatorString(array('max_length' => 255)),
      'sf_guard_user_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('User'), 'required' => false)),
      'description'        => new sfValidatorString(array('required' => false)),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
      'slug'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'contacts_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Contact', 'required' => false)),
      'professionals_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Professional', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorDoctrineUnique(array('model' => 'Group', 'column' => array('name', 'sf_guard_user_id'))),
        new sfValidatorDoctrineUnique(array('model' => 'Group', 'column' => array('slug'))),
      ))
    );

    $this->widgetSchema->setNameFormat('group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Group';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['contacts_list']))
    {
      $this->setDefault('contacts_list', $this->object->Contacts->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['professionals_list']))
    {
      $this->setDefault('professionals_list', $this->object->Professionals->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveContactsList($con);
    $this->saveProfessionalsList($con);

    parent::doSave($con);
  }

  public function saveContactsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['contacts_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Contacts->getPrimaryKeys();
    $values = $this->getValue('contacts_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Contacts', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Contacts', array_values($link));
    }
  }

  public function saveProfessionalsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['professionals_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Professionals->getPrimaryKeys();
    $values = $this->getValue('professionals_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Professionals', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Professionals', array_values($link));
    }
  }

}

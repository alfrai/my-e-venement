<?php

/**
 * Addressable form base class.
 *
 * @method Addressable getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAddressableForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'name'       => new sfWidgetFormInputText(),
      'address'    => new sfWidgetFormTextarea(),
      'postalcode' => new sfWidgetFormInputText(),
      'city'       => new sfWidgetFormInputText(),
      'country'    => new sfWidgetFormInputText(),
      'email'      => new sfWidgetFormInputText(),
      'npai'       => new sfWidgetFormInputCheckbox(),
      'latitude'   => new sfWidgetFormInputText(),
      'longitude'  => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
      'slug'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'       => new sfValidatorString(array('max_length' => 255)),
      'address'    => new sfValidatorString(array('required' => false)),
      'postalcode' => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'city'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'country'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'email'      => new sfValidatorEmail(array('max_length' => 255, 'required' => false)),
      'npai'       => new sfValidatorBoolean(array('required' => false)),
      'latitude'   => new sfValidatorPass(array('required' => false)),
      'longitude'  => new sfValidatorPass(array('required' => false)),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
      'slug'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Addressable', 'column' => array('slug')))
    );

    $this->widgetSchema->setNameFormat('addressable[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Addressable';
  }

}

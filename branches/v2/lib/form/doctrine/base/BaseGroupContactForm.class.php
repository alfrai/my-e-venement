<?php

/**
 * GroupContact form base class.
 *
 * @method GroupContact getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseGroupContactForm extends GroupDetailForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['contact_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['contact_id'] = new sfValidatorChoice(array('choices' => array($this->getObject()->get('contact_id')), 'empty_value' => $this->getObject()->get('contact_id'), 'required' => false));

    $this->widgetSchema->setNameFormat('group_contact[%s]');
  }

  public function getModelName()
  {
    return 'GroupContact';
  }

}

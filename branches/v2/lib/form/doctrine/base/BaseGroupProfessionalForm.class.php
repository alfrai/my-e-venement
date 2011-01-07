<?php

/**
 * GroupProfessional form base class.
 *
 * @method GroupProfessional getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseGroupProfessionalForm extends GroupDetailForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['professional_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['professional_id'] = new sfValidatorChoice(array('choices' => array($this->getObject()->get('professional_id')), 'empty_value' => $this->getObject()->get('professional_id'), 'required' => false));

    $this->widgetSchema->setNameFormat('group_professional[%s]');
  }

  public function getModelName()
  {
    return 'GroupProfessional';
  }

}

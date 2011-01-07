<?php

/**
 * ContactPhonenumber form base class.
 *
 * @method ContactPhonenumber getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseContactPhonenumberForm extends PhonenumberForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['contact_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Contact'), 'add_empty' => false));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Contact')));

    $this->widgetSchema->setNameFormat('contact_phonenumber[%s]');
  }

  public function getModelName()
  {
    return 'ContactPhonenumber';
  }

}

<?php

/**
 * OrganismPhonenumber form base class.
 *
 * @method OrganismPhonenumber getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseOrganismPhonenumberForm extends PhonenumberForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['organism_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Organism'), 'add_empty' => false));
    $this->validatorSchema['organism_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Organism')));

    $this->widgetSchema->setNameFormat('organism_phonenumber[%s]');
  }

  public function getModelName()
  {
    return 'OrganismPhonenumber';
  }

}

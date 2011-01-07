<?php

/**
 * Organism form base class.
 *
 * @method Organism getObject() Returns the current form's model object
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseOrganismForm extends AddressableForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['url'] = new sfWidgetFormInputText();
    $this->validatorSchema['url'] = new sfValidatorString(array('max_length' => 255, 'required' => false));

    $this->widgetSchema   ['description'] = new sfWidgetFormTextarea();
    $this->validatorSchema['description'] = new sfValidatorString(array('required' => false));

    $this->widgetSchema   ['organism_category_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Category'), 'add_empty' => true));
    $this->validatorSchema['organism_category_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Category'), 'required' => false));

    $this->widgetSchema->setNameFormat('organism[%s]');
  }

  public function getModelName()
  {
    return 'Organism';
  }

}

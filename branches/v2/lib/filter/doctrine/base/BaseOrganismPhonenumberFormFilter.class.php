<?php

/**
 * OrganismPhonenumber filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseOrganismPhonenumberFormFilter extends PhonenumberFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['organism_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Organism'), 'add_empty' => true));
    $this->validatorSchema['organism_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Organism'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('organism_phonenumber_filters[%s]');
  }

  public function getModelName()
  {
    return 'OrganismPhonenumber';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'organism_id' => 'ForeignKey',
    ));
  }
}

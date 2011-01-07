<?php

/**
 * Organism filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseOrganismFormFilter extends AddressableFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['url'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['url'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['description'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['description'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['organism_category_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Category'), 'add_empty' => true));
    $this->validatorSchema['organism_category_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Category'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('organism_filters[%s]');
  }

  public function getModelName()
  {
    return 'Organism';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'url' => 'Text',
      'description' => 'Text',
      'organism_category_id' => 'ForeignKey',
    ));
  }
}

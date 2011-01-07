<?php

/**
 * GroupProfessional filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseGroupProfessionalFormFilter extends GroupDetailFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['professional_id'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['professional_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'GroupProfessional', 'column' => 'professional_id'));

    $this->widgetSchema->setNameFormat('group_professional_filters[%s]');
  }

  public function getModelName()
  {
    return 'GroupProfessional';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'professional_id' => 'Number',
    ));
  }
}

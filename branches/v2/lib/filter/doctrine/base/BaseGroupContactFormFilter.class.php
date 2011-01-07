<?php

/**
 * GroupContact filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseGroupContactFormFilter extends GroupDetailFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['contact_id'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'GroupContact', 'column' => 'contact_id'));

    $this->widgetSchema->setNameFormat('group_contact_filters[%s]');
  }

  public function getModelName()
  {
    return 'GroupContact';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'contact_id' => 'Number',
    ));
  }
}

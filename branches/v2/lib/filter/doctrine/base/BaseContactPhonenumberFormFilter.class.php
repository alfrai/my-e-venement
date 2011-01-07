<?php

/**
 * ContactPhonenumber filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseContactPhonenumberFormFilter extends PhonenumberFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['contact_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Contact'), 'add_empty' => true));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Contact'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('contact_phonenumber_filters[%s]');
  }

  public function getModelName()
  {
    return 'ContactPhonenumber';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'contact_id' => 'ForeignKey',
    ));
  }
}

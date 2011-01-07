<?php

/**
 * Contact filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseContactFormFilter extends AddressableFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['firstname'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['firstname'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['title'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['title'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['description'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['description'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['password'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['password'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['groups_list'] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Group'));
    $this->validatorSchema['groups_list'] = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Group', 'required' => false));

    $this->widgetSchema->setNameFormat('contact_filters[%s]');
  }

  public function addGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.GroupContact GroupContact')
      ->andWhereIn('GroupContact.group_id', $values)
    ;
  }

  public function getModelName()
  {
    return 'Contact';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'firstname' => 'Text',
      'title' => 'Text',
      'description' => 'Text',
      'password' => 'Text',
      'groups_list' => 'ManyKey',
    ));
  }
}

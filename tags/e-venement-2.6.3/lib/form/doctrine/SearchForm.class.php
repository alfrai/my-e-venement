<?php

/**
 * Search form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SearchForm extends BaseFormDoctrine
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','Asset'));
    use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js');
    use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css');
    
    // organism
    $this->validatorSchema['organism_id'] = new sfValidatorInteger(array('required' => false));
    $this->widgetSchema   ['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
    ));
    $this->widgetSchema   ['organism_category_list'] = new sfWidgetFormDoctrineChoice(array(
      'model'       => 'OrganismCategory',
      'order_by'    => array('name, slug',''),
      'multiple'    => true,
    ));
    $this->validatorSchema['organism_category_list'] = new sfValidatorDoctrineChoice(array(
      'model'       => 'OrganismCategory',
      'required'    => false,
      'multiple'    => true,
    ));
    
    // groupes
    $choices = array();
    $q = GroupTable::getInstance()->createQuery('g');
    $coll = $q->execute();
    foreach ( $coll as $key => $value )
    {
      $username = $value['User'] ? $value['User']['name'].' ('.$value['User']['username'].')' : 'Groupes communs';
      $choices[$username][$value['id']] = $value['name'];
    }
    $this->widgetSchema   ['groups_list'] = new sfWidgetFormChoice(array(
      'choices'   => $choices,
      'multiple'  => true,
    ));
    $this->validatorSchema['groups_list'] = new sfValidatorDoctrineChoice(array(
      'model'       => 'Group',
      'required'    => false,
      'multiple'    => true,
    ));
    
    // contact
    $this->widgetSchema   ['contact_name'] = new sfWidgetFormInputText();
    $this->validatorSchema['contact_name'] = new sfValidatorString(array(
      'required'  => false,
      'trim'      => true,
    ));
    $this->widgetSchema   ['contact_firstname'] = new sfWidgetFormInputText();
    $this->validatorSchema['contact_firstname'] = new sfValidatorString(array(
      'required'  => false,
      'trim'      => true,
    ));
    $this->widgetSchema   ['contact_description'] = new sfWidgetFormInputText();
    $this->validatorSchema['contact_description'] = new sfValidatorString(array(
      'required'  => false,
      'trim'      => true,
    ));
    
    // contact / organism mix
    $this->widgetSchema   ['postalcode'] = new sfWidgetFormInputText();
    $this->validatorSchema['postalcode'] = new sfValidatorString(array(
      'required'  => false,
      'trim'      => true,
    ));
    $this->widgetSchema   ['city'] = new sfWidgetFormInputText();
    $this->validatorSchema['city'] = new sfValidatorString(array(
      'required'  => false,
      'trim'      => true,
    ));
    $this->widgetSchema   ['country'] = new sfWidgetFormInputText();
    $this->validatorSchema['country'] = new sfValidatorString(array(
      'required'  => false,
      'trim'      => true,
    ));
    
    // more fields
    $this->widgetSchema   ['npai'] = new sfWidgetFormInputCheckbox();
    $this->validatorSchema['npai'] = new sfValidatorBoolean(array('required'  => false));
    $this->widgetSchema   ['no_address'] = new sfWidgetFormInputCheckbox();
    $this->validatorSchema['no_address'] = new sfValidatorBoolean(array('required'  => false));
    $this->widgetSchema   ['no_email'] = new sfWidgetFormInputCheckbox();
    $this->validatorSchema['no_email'] = new sfValidatorBoolean(array('required'  => false));
    
    parent::configure();
  }
  
  public function getModelName()
  {
    // dummy for abstract class
    return 'Contact';
  }
}

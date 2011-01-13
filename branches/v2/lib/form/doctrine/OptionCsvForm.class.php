<?php

/**
 * OptionCsv form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionCsvForm extends BaseOptionCsvForm
{
  /**
   * @see OptionForm
   */
  public function configure()
  {
    parent::configure();
    $this->model = 'OptionCsv';
    
    self::enableCSRFProtection();
    
    foreach ( array('type','name','value','sf_guard_user_id','created_at','updated_at',) as $id )
    {
      unset($this->widgetSchema   [$id]);
      unset($this->validatorSchema[$id]);
    }
    
    $this->widgets = array(
      'contact' => array(
        'title' => 'Title',
        'name' => 'Name',
        'firstname' => 'Firstname',
        'address' => 'Address',
        'postalcode' => 'Postalcode',
        'city' => 'City',
        'country' => 'Country',
        'npai' => 'NPAI',
        'email' => 'Email',
        'description' => 'Keywords',
        'phonename' => 'Phonetype',
        'phonenumber' => 'Phonenumber',
      ),
      'organism' => array(
        'organism_category' => 'Organism category',
        'organism_name' => 'Name',
        'organism_address' => 'Address',
        'organism_postalcode' => 'Postalcode',
        'organism_city' => 'City',
        'organism_country' => 'Country',
        'organism_email' => 'Email',
        'organism_url' => 'URL',
        'organism_npai' => 'NPAI',
        'organism_description' => 'Description',
        'organism_phonename' => 'Phonetype',
        'organism_phonenumber' => 'Phonenumber',
      ),
      'professional' => array(
        'professional_number' => 'Professional phonenumber',
        'professional_email' => 'Professional email',
        'professional_type_name' => 'Professional type',
        'professional_name' => 'Professional',
        'professional_department' => 'Department',
      ),
      'extra' => array(
        'information' => 'More informations',
        'microsoft'   => 'Microsoft-Compatible',
        'noheader'    => 'No header',
        'tunnel'      => 'Prefer professional informations',
      ),
      'out' => array(
        'select-all'  => 'Select All',
      ),
    );

    foreach ( $this->widgets as $fieldset )
    foreach ( $fieldset as $name => $value )
    {
      $this->widgetSchema[$name]    = new sfWidgetFormInputCheckbox(array(
          'value_attribute_value' => $value,
          'label'                 => $value,
        ),
        array(
          'title'                 => $value,
      ));
      $this->validatorSchema[$name] = new sfValidatorBoolean(array('true_values' => array($value)));
    }
  }
}

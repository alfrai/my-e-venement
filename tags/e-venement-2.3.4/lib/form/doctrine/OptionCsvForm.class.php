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
  
  public static function orderData($data)
  {
	  $fields = array(
  	  'title'     => 'Title',
    	'name'      => 'Name',
	    'firstname' => 'Firstname',
  	  'address'   => 'Address',
    	'postalcode'=> 'Postalcode',
	    'city'      => 'City',
  	  'country'   => 'Country',
    	'npai'      => 'Npai',
	    'email'               => 'email',
  	  'description'         => 'Keywords',
    	'phonename'           => 'Phonetype',
	    'phonenumber'         => 'Phonenumber',
  	  'organism_category'   => 'Category of organism',
    	'organism_name'       => 'Organism',
	    'professional_department' => 'Department',
  	  'professional_number' => 'Professional phone',
    	'professional_email'  => 'Professional email',
	    'professional_type_name' => 'Type of function',
  	  'professional_name'   => 'Function',
    	'organism_address'    => 'Address',
	    'organism_postalcode' => 'Postalcode',
  	  'organism_city'       => 'City',
    	'organism_country'    => 'Country',
	    'organism_email'      => 'Email',
  	  'organism_url'        => 'URL',
	    'organism_npai'       => 'Npai',
 		  'organism_description'=> 'Description',
    	'organism_phonename'  => 'Phonetype',
    	'organism_phonenumber'=> 'Phonenumber',
    	'information'         => 'Informations',
  	);
		
    // ordering
    $ordered = array();
    foreach ( $fields as $fieldname => $field )
 	  if ( in_array($fieldname,$data) )
			$ordered[] = $fieldname;
		else if ( isset($data[$fieldname]) )
			$ordered[$fieldname] = $data[$fieldname];
    
    return $ordered;
  }
  
  public static function getDBOptions()
  {
    $options = array('field' => array(), 'option' => array());
    foreach ( self::buildOptionsQuery()->fetchArray() as $option )
      $options[$option['name']][] = $option['value'];
    
    $options['field'] = self::orderData($options['field']);
    
    return $options;
  }
  
  protected static function buildOptionsQuery()
  {
    $q = Doctrine::getTable('OptionCsv')->createQuery();
    if ( sfContext::getInstance()->getUser() instanceof sfGuardSecurityUser )
      $q->where('sf_guard_user_id = ?',sfContext::getInstance()->getUser()->getId());
    else
      $q->where('sf_guard_user_id IS NULL');
    
    return $q;
  }
  
  public static function tunnelingContact($contact)
  {
      if ( $contact['organism_postalcode'] && $contact['organism_city'] && !$contact['organism_npai'] )
      {
        $arr = array(
          'organism_address'    => 'address',
          'organism_postalcode' => 'postalcode',
          'organism_city'       => 'city',
          'organism_country'    => 'country',
          'organism_npai'       => 'npai',
        );
        foreach ( $arr as $origin => $target )
        {
          $contact[$target] = $contact[$origin];
          unset($contact[$origin]);
        }
      }
      
      if ( $contact['organism_email'] ) $contact['email'] = $contact['organism_email'];
      if ( $contact['professional_email'] ) $contact['email'] = $contact['professional_email'];
      unset($contact['organism_email'], $contact['professional_email']);
      
      if ( $contact['organism_phonenumber'] )
      {
        $contact['phonename']    = $contact['organism_phonename'];
        $contact['phonenumber']  = $contact['organism_phonenumber'];
      }
      if ( $contact['professional_number'] )
      {
        $contact['phonename']    = 'Professional';
        $contact['phonenumber']  = $contact['professional_number'];
      }
      unset($contact['organism_phonename'], $contact['organism_phonenumber'], $contact['professional_number']);
      
      return $contact;
  }
}

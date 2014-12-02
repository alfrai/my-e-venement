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
        '__YOBs__year'   => 'Birthdays',
        '__Groups__name' => 'Groups',
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
        '__Professionals__Organism__Groups__name' => "Organism's groups",
      ),
      'professional' => array(
        'professional_number' => 'Professional phonenumber',
        'professional_email' => 'Professional email',
        'professional_type_name' => 'Professional type',
        'professional_name' => 'Professional',
        'professional_department' => 'Department',
        '__Professionals__Groups__name' => 'Professional groups',
        'professional_important' => 'Close contact / Important organism',
      ),
      'extra' => array(
        'information' => 'More informations',
        'microsoft'   => 'Microsoft-Compatible',
        'noheader'    => 'No header',
        'always_pro'  => 'Professional data: always',
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
	    '__YOBs__year'        => 'Birthdays',
	    '__Groups__name'      => 'Groups',
  	  'organism_category'   => 'Category of organism',
    	'organism_name'       => 'Organism',
	    'professional_department' => 'Department',
  	  'professional_number' => 'Professional phone',
    	'professional_email'  => 'Professional email',
	    'professional_type_name' => 'Type of function',
  	  'professional_name'   => 'Function',
  	  '__Professionals__Groups__name' => "Professionals groups",
  	  'professional_important' => 'Close contact / Important organism',
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
    	'__Professionals__Organism__Groups__name' => "Organism's group",
    	'information'         => 'Informations',
  	);
		
    // ordering
    $ordered = array();
    foreach ( $fields as $fieldname => $field )
 	  if ( in_array($fieldname,$data) )
			$ordered[] = $fieldname;
		elseif ( !$data )
			$ordered[] = $fieldname;
    
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
      
      if ( isset($contact['organism_email']) && $contact['organism_email'] ) $contact['email'] = $contact['organism_email'];
      if ( isset($contact['professional_email']) && $contact['professional_email'] ) $contact['email'] = $contact['professional_email'];
      unset($contact['organism_email'], $contact['professional_email']);
      
      if ( isset($contact['organism_phonenumber']) && $contact['organism_phonenumber'] )
      {
        $contact['phonename']    = $contact['organism_phonename'];
        $contact['phonenumber']  = $contact['organism_phonenumber'];
      }
      if ( isset($contact['professional_number']) && $contact['professional_number'] )
      {
        $contact['phonename']    = 'Professional';
        $contact['phonenumber']  = $contact['professional_number'];
      }
      unset($contact['organism_phonename'], $contact['organism_phonenumber'], $contact['professional_number']);
      
      return $contact;
  }
  
  static function getImplodedData($data, $fields, $separator = "\n")
  {
    if ( !isset($fields[0]) )
      return false;
    if ( !isset($data[$fields[0]]) && !isset($data[0]) )
      return false;
    
    if ( isset($data[0]) )
    {
      $tmp = array();
      foreach ( $data as $buf )
        $tmp[] = self::getImplodedData($buf,$fields);
      return implode($separator,$tmp);
    }
    
    if ( is_array($data[$fields[0]]) ) // in case there is subelements
    {
      if ( count($fields) > 1 ) // if there lasts some subdata to get back
      {
        $tmp = $fields[0];
        unset($fields[0]);
        return self::getImplodedData($data[$tmp],array_values($fields));
      }
      else // no subdata, implode directy
        return implode($separator,$data);
    }
    else // no subelement ? get back data directly
      return $data[$fields[0]];
  }
}

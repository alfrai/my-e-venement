<?php

/**
 * Organism form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OrganismForm extends BaseOrganismForm
{
  /**
   * @see AddressableForm
   */
  public function configure()
  {
    $this->widgetSchema   ['phone_number'] = new sfWidgetFormInputText();
    $this->validatorSchema['phone_number'] = new sfValidatorPass(array('required' => false));
    
    $this->widgetSchema   ['phone_type']   = new liWidgetFormDoctrineJQueryAutocompleterGuide(array(
      'model' => 'PhoneType',
      'url'   => url_for('phone_type/ajax'),
      'method_for_query' => 'findOneByName',
    ));
    $this->widgetSchema   ['phone_type']->getStylesheets();
    $this->widgetSchema   ['phone_type']->getJavascripts();
    $this->validatorSchema['phone_type'] = new sfValidatorPass(array(
      'required' => false,
    ));
    
    $this->widgetSchema['groups_list']->setOption(
      'order_by',
      array('u.id IS NULL DESC, u.username, name','')
    );
    
    $this->validatorSchema['url'] = new sfValidatorUrl(array(
      'required' => false,
    ));
    
    // adding artificial mandatory fields
    if ( is_array($force = sfConfig::get('app_options_force_fields', array())) )
    foreach ( $force as $field )
    {
      if ( isset($this->validatorSchema[$field]) )
        $this->validatorSchema[$field]->setOption('required', true);
    }

    parent::configure();
  }    
  
  
  public function saveGroupsList($con = null)
  {
    $this->correctGroupsListWithCredentials();
    parent::saveGroupsList($con);
  }
}

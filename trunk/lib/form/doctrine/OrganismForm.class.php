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
    
    $this->validatorSchema['url'] = new liValidatorUrl(array(
      'required' => false,
    ));
    
    $this->widgetSchema['organism_category_id']->setOption('order_by',array('name',''));
    $this->widgetSchema['professional_id']
      ->setOption('query', Doctrine::getTable('Professional')->createQuery('p')->andWhere('o.id = ?', $this->object->id))
      ->setOption('order_by',array('c.name, c.firstname',''))
      ->setOption('expanded', true);
    
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

  public function displayOnly($fieldname = NULL)
  {
    unset(
      $this->widgetSchema['emails_list'],
      $this->widgetSchema['groups_list'],
      $this->widgetSchema['events_list'],
      $this->widgetSchema['manifestations_list']
    );
    
    // BUG: 2013-04-12
    if ( is_null($fieldname) )
      return $this;
    
    if ( !($this->widgetSchema[$fieldname] instanceof sfWidgetForm) )
      throw new liEvenementException('Fieldname "'.$fieldname.'" not found.');
    
    foreach ( $this->widgetSchema->getFields() as $name => $widget )
    {
      if ( $name != $fieldname )
        $this->widgetSchema[$name] = new sfWidgetFormInputHidden();
    }
    
    return $this;
  }
}

<?php

/**
 * Base project form.
 * 
 * @package    e-venement
 * @subpackage form
 * @author     Your name here 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class StatsCriteriasForm extends BaseForm
{
  public function configure()
  {
    $this->widgetSchema['dates'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'template'  => 'du %from_date%<br/> au %to_date%',
    ));
    $this->validatorSchema['dates'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false)),
      'to_date'   => new sfValidatorDate(array('required' => false)),
      'required' => false,
    ));
    
    $this->widgetSchema->setNameFormat('criterias[%s]');
    $this->disableCSRFProtection();
  }
  
  public function addManifestationCriteria()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    
    $this->widgetSchema['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'url' => cross_app_url_for('event','manifestation/ajax'),
      'model' => 'Manifestation',
      'config'=> '{ max: 50 }',
    ));
    $this->validatorSchema['manifestations_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'required' => false,
      'multiple' => true,
    ));
  }
  public function addEventCriterias()
  {
    $this->widgetSchema['workspaces_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Workspace',
      'order_by' => array('name',''),
      'multiple' => true,
    ));
    $this->validatorSchema['workspaces_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
      'required' => false,
    ));

    $this->widgetSchema['meta_events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'order_by' => array('name',''),
      'multiple' => true,
    ));
    $this->validatorSchema['meta_events_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MetaEvent',
      'multiple' => true,
      'required' => false,
    ));
    return $this;
  }
  
  public function addUsersCriteria()
  {
    $this->widgetSchema['users'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'sfGuardUser',
      'order_by'  => array('first_name, last_name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['users'] = new sfValidatorDoctrineChoice(array(
      'model' => 'sfGuardUser',
      'multiple' => true,
      'required' => false,
    ));
    return $this;
  }
  
  public function addAccountingCriterias()
  {
    $this->widgetSchema['accounting_vat'] = new sfWidgetFormInput();
    $this->validatorSchema['accounting_vat'] = new sfValidatorInteger(array(
      'min' => 0,
      'max' => 100,
    ));
    $this->widgetSchema['accounting_unit_price'] = new sfWidgetFormInput();
    $this->validatorSchema['accounting_unit_price'] = new sfValidatorInteger(array(
      'min' => 0,
    ));
    return $this;
  }
  
  public function addWithContactCriteria()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $choices = array(
      ''    => __('yes or no',null,'sf_admin'),
      'yes' => __('yes',null,'sf_admin'),
      'no'  => __('no',null,'sf_admin'),
    );
    
    $this->widgetSchema   ['with_contact'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
    ));
    $this->validatorSchema['with_contact'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
    ));
    return $this;
  }
  
  public function addIntervalCriteria()
  {
    $this->widgetSchema   ['interval'] = new sfWidgetFormInput(array(
      'default'   => 1,
    ));
    $this->validatorSchema['interval'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    return $this;
  }
}

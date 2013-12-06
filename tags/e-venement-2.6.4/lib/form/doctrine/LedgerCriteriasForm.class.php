<?php

/**
 * Base project form.
 * 
 * @package    e-venement
 * @subpackage form
 * @author     Your name here 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class LedgerCriteriasForm extends BaseForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    
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

    $q = Doctrine::getTable('sfGuardUser')->createQuery('u');
    if ( !sfContext::getInstance()->getUser()->hasCredential('tck-ledger-all-users') )
      $q->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema['users'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'sfGuardUser',
      'query'     => $q,
      'order_by'  => array('first_name, last_name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['users'] = new sfValidatorDoctrineChoice(array(
      'model' => 'sfGuardUser',
      'multiple' => true,
      'required' => false,
      'query'     => $q,
    ));
    
    $this->widgetSchema['manifestations'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event','manifestation/ajax'),
      'config'=> '{ max: 50 }',
    ));
    $this->validatorSchema['manifestations'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['workspaces'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Workspace',
      'order_by'  => array('name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['workspaces'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['not-yet-printed'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 'yes',
    ));
    $this->validatorSchema['not-yet-printed'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    
    $this->widgetSchema['tck_value_date_payment'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 'yes',
    ));
    $this->validatorSchema['tck_value_date_payment'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    
    $this->widgetSchema->setNameFormat('criterias[%s]');
    $this->disableCSRFProtection();
  }
}

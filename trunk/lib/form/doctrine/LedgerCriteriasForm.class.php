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

    $this->widgetSchema['users'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'sfGuardUser',
      'add_empty' => true,
      'order_by'  => array('first_name, last_name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['users'] = new sfValidatorDoctrineChoice(array(
      'model' => 'sfGuardUser',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['manifestations'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event','manifestation/ajax'),
    ));
    $this->validatorSchema['manifestations'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['workspaces'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Workspace',
      'add_empty' => true,
      'order_by'  => array('name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['workspaces'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
      'required' => false,
    ));
 
    $this->widgetSchema->setNameFormat('criterias[%s]');
    $this->disableCSRFProtection();
  }
}

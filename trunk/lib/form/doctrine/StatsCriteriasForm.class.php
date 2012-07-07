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

    $this->widgetSchema['workspaces_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
      'add_empty' => true,
    ));
    $this->validatorSchema['workspaces_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
    ));

    $this->widgetSchema['meta_events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'multiple' => true,
      'add_empty' => true,
    ));
    $this->validatorSchema['meta_events_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MetaEvent',
      'multiple' => true,
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
    
    $this->widgetSchema->setNameFormat('criterias[%s]');
    $this->disableCSRFProtection();
  }
}

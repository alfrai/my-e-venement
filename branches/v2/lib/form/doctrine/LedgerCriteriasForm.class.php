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
    $this->widgetSchema['dates'] = new sfWidgetFormDateRange(array(
      'from_date' => new sfWidgetFormDate(),
      'to_date'   => new sfWidgetFormDate(),
    ));
    $this->validatorSchema['dates'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(),
      'to_date'   => new sfValidatorDate(),
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
    ));
    
    $this->widgetSchema->setNameFormat('criterias[%s]');
  }
}

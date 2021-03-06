<?php

/**
 * PhoneType form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PhoneTypeForm extends BasePhoneTypeForm
{
  /**
   * @see ModelTypeForm
   */
  public function configure()
  {
    parent::configure();
    $this->validatorSchema['type']->setOption('required', false);
    
    $choices = array('' => '');
    foreach (sfConfig::get('app_phone_mask') as $mask) {
      $choices[$mask] = $mask;
    }
    
    $this->widgetSchema['mask'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
    ));
    
  }
  public function preSave()
  {
    parent::preSave();
    $this->type = 'phone';
  }
}

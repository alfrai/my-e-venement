<?php

/**
 * OrganismPhonenumber form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OrganismPhonenumberForm extends BaseOrganismPhonenumberForm
{
  /**
   * @see PhonenumberForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    parent::configure();
    $this->widgetSchema['mask'] = new sfWidgetFormInputText(array(
      'label' => __('Mask'), 
    ));
    $this->validatorSchema['mask'] = new sfValidatorString(array(
      'required' => false
    ));    
  }
}

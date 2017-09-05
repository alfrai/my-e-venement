<?php

/**
 * AdminTask form.
 *
 * @package    evenement
 * @subpackage kiosk
 * @author     Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class AdminTaskForm extends BaseAdminTaskForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();

    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');

    $this->widgetSchema['type'] = new sfWidgetFormSelect(array(
    	'choices' => array(
    		'client_receipt' => __('Client receipt'),
    		'seller_receipt' => __('Seller receipt'),
    		'pin'            => __('Pin'),
    	)
    ));

    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputHidden();

    $this->widgetSchema['pin'] = new sfWidgetFormInputText();
  }
}

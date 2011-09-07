<?php

/**
 * Payment form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PaymentForm extends BasePaymentForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    unset($this->widgetSchema['sf_guard_user_id']);
    unset($this->validatorSchema['sf_guard_user_id']);
    
    unset($this->widgetSchema['version']);
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['payment_method_id']->setOption('add_empty',true);
  }
}

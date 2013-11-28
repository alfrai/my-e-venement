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
  protected $removed_widgets = array(), $removed_validators = array();
  protected $noTimestampableUnset = true;
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    $this->removed_widgets['sf_guard_user_id'] = $this->widgetSchema['sf_guard_user_id'];
    $this->removed_validators['sf_guard_user_id'] = $this->validatorSchema['sf_guard_user_id'];
    unset($this->widgetSchema['sf_guard_user_id']);
    unset($this->validatorSchema['sf_guard_user_id']);
    
    unset($this->widgetSchema['version']);
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputHidden();
    
    $this->widgetSchema   ['payment_method_id']->setOption('add_empty',true);
    $this->widgetSchema   ['payment_method_id']->setOption('order_by',array('name',''));
    $this->widgetSchema   ['payment_method_id']->setOption('query',$q = Doctrine::getTable('PaymentMethod')->createQuery('pm')
      ->andWhere('pm.member_card_linked != true OR ?',sfContext::getInstance()->getUser()->hasCredential('tck-member-cards')));
    $this->validatorSchema['payment_method_id']->setOption('query',$q);
    
    unset($this->widgetSchema['updated_at']);
    unset($this->validatorSchema['updated_at']);
    
    $this->widgetSchema['created_at'] = new liWidgetFormDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture()));
    $this->validatorSchema['created_at']->setOption('required',false);
    
    $this->widgetSchema['member_card_id'] = new sfWidgetFormInputHidden;
  }
  
  public function setWithUserId()
  {
    $this->widgetSchema['sf_guard_user_id'] = $this->removed_widgets['sf_guard_user_id'];
    $this->validatorSchema['sf_guard_user_id'] = $this->removed_validators['sf_guard_user_id'];
  }
}

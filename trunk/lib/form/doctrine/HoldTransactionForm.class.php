<?php

/**
 * HoldTransaction form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class HoldTransactionForm extends BaseHoldTransactionForm
{
  public function configure()
  {
    $this->validatorSchema['transaction_id']->setOption('required', false);
    $this->widgetSchema   ['transaction_id'] = new sfWidgetFormInputHidden;
    
    $this->widgetSchema['hold_id']->setOption('order_by', array('ht.name', ''));
    
    if ( $this->object->isNew() )
      return;
    // ONLY NOT NEW OBJECTS UNTIL NOW
    
    $this->widgetSchema['hold_id'] = new sfWidgetFormInputHidden;
  }
}

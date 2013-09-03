<?php

/**
 * Order filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OrderFormFilter extends BaseOrderFormFilter
{
  /**
   * @see AccountingFormFilter
   */
  public function configure()
  {
    parent::configure();
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $this->widgetSchema['created_at'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormDateText(),
      'to_date'   => new liWidgetFormDateText(),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span>'),
    ));
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText();
  }
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }
}

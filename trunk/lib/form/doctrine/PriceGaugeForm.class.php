<?php

/**
 * PriceGauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceGaugeForm extends BasePriceGaugeForm
{
  public function configure()
  {
    $this->widgetSchema['gauge_id'] = new sfWidgetFormInputHidden;
    $this->widgetSchema['price_id'] = new sfWidgetFormInputHidden;
    $this->widgetSchema['value']->setAttribute('pattern', '\d+(\.\d+){0,1}');
  }
}

<?php

/**
 * PriceProduct form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceProductForm extends BasePriceProductForm
{
  public function configure()
  {
    $this->widgetSchema['price_id'] = new sfWidgetFormInputHidden;
    $this->useFields(array('value', 'price_id'));
  }
}

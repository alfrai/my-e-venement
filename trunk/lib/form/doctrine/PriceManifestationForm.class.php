<?php

/**
 * PriceManifestation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceManifestationForm extends BasePriceManifestationForm
{
  private $orig_widgets = array();
  
  public function configure()
  {
    $this->widgetSchema['manifestation_id']->setOption('add_empty',true);
    $this->widgetSchema['price_id']->setOption('add_empty',true);
    $this->validatorSchema['value']->setOption('required',false);
  }
  public function setHidden($hides = array('manifestation_id','price_id'))
  {
    foreach ( $hides as $hide )
      $this->widgetSchema[$hide] = new sfWidgetFormInputHidden();
    return $this;
  }
}

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
  }
  public function setHidden($hidden = true)
  {
    if ( $hidden )
    {
      $orig_widgets['manifestation_id'] = $this->widgetSchema['manifestation_id'];
      $orig_widgets['price_id'] = $this->widgetSchema['price_id'];
      $this->widgetSchema['manifestation_id'] = new sfWidgetFormInputHidden();
      $this->widgetSchema['price_id'] = new sfWidgetFormInputHidden();
    }
    else
    {
      $this->widgetSchema['manifestation_id'] = $orig_widgets['manifestation_id'];
      $this->widgetSchema['price_id'] = $orig_widgets['price_id'];
    }
    return $this;
  }
}

<?php

/**
 * Manifestation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Manifestation extends PluginManifestation
{
  public function getName()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N','Date'));
    return $this->Event->name.' '.__('at').' '.format_datetime($this->happens_at);
  }
  public function __toString()
  {
    return $this->getName();
  }
  
  public function postInsert($event)
  {
    if ( $this->PriceManifestations->count() == 0 )
    foreach ( Doctrine::getTable('Price')->createQuery()->execute() as $price )
    {
      $pm = PriceManifestation::createPrice($price);
      $pm->manifestation_id = $this->id;
      $pm->save();
      $this->PriceManifestations[] = $pm;
    }
    $this->save();
    
    parent::postInsert($event);
  }
}

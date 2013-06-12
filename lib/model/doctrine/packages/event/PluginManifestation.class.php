<?php

/**
 * PluginManifestation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginManifestation extends BaseManifestation implements liMetaEventSecurityAccessor
{
  public function duplicate($save = true)
  {
    $manif = $this->copy();
    foreach ( array('id', 'updated_at', 'created_at', 'sf_guard_user_id') as $property )
      $manif->$property = NULL;
    foreach ( array('Gauges', 'PriceManifestations', 'Organizers') as $subobjects )
    foreach ( $this->$subobjects as $subobject )
    {
      $collection = $manif->$subobjects;
      $collection[] = $subobject->copy();
    }
    
    if ( $save )
      $manif->save();
    
    return $manif;
  }
  
  public function preSave($event)
  {
    if ( intval($this->duration).'' != ''.$this->duration )
    {
      $this->duration = intval(strtotime($this->duration.'+0',0));
    }
    parent::preSave($event);
  }
  
  public function postInsert($event)
  {
    if ( $this->PriceManifestations->count() == 0 )
    foreach ( Doctrine::getTable('Price')->createQuery()->execute() as $price )
    {
      $pm = PriceManifestation::createPrice($price);
      $pm->manifestation_id = $this->id;
      //$pm->save();
      $this->PriceManifestations[] = $pm;
    }
    $this->save();
    
    parent::postInsert($event);
  }
  
  public function getDurationHR()
  {
    if ( intval($this->duration).'' != ''.$this->duration )
      return $this->duration;
    
    $hours = floor($this->duration/3600);
    $minutes = floor($this->duration%3600/60) > 9 ? floor($this->duration%3600/60) : '0'.floor($this->duration%3600/60);
    return $hours.':'.$minutes;
  }
  
  public function getMEid()
  {
    return $this->Event->getMEid();
  }
  
}

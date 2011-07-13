<?php

/**
 * Ticket
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Ticket extends PluginTicket
{
  public function preSave($event)
  {
    if ( is_null($this->price_id) && !is_null($this->price_name) && !is_null($this->manifestation_id) )
    {
      $q = Doctrine::getTable('PriceManifestation')->createQuery('pm')
        ->leftJoin('pm.Manifestation m')
        ->leftJoin('pm.Price p')
        ->andWhere('m.id = ?',$this->manifestation_id)
        ->andWhere('p.name = ?',$this->price_name)
        ->orderBy('pm.updated_at DESC');
      $pm = $q->execute()->get(0);
      $this->price_id = $pm->price_id;
      $this->value    = $pm->value;
    }
    parent::preSave($event);
  }
  
  public function getBarcode($salt = '')
  {
    return md5('#'.$this->id.'-'.$salt);
  }
  
  public function __toString()
  {
    return '#'.$this->id;
  }
}

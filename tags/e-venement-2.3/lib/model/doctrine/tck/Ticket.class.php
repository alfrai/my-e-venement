<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
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
    if ( (is_null($this->manifestation_id) || is_null($this->value) || is_null($this->price_id)) && (!is_null($this->price_name) || !is_null($this->price_id)) && !is_null($this->gauge_id) )
    {
      $q = Doctrine::getTable('PriceManifestation')->createQuery('pm')
        ->leftJoin('pm.Manifestation m')
        ->leftJoin('pm.Price p')
        ->leftJoin('m.Gauges g')
        ->andWhere('g.id = ?',$this->gauge_id)
        ->orderBy('pm.updated_at DESC');
      
      if ( !is_null($this->price_id) )
        $q->andWhere('p.id = ?',$this->price_id);
      else
        $q->andWhere('p.name = ?',$this->price_name);
      
      $pm = $q->fetchOne();
      if ( !$pm )
        throw new liEvenementException('Object not found.');
      
      if ( is_null($this->manifestation_id) )
        $this->manifestation_id = $pm->manifestation_id;
      if ( is_null($this->price_name) )
        $this->price_name = $pm->Price->name;
      if ( is_null($this->price_id) )
        $this->price_id = $pm->price_id;
      if ( is_null($this->value) )
        $this->value    = $pm->value;
    }
    
    // the transaction's last update
    $this->Transaction->updated_at = NULL;
    
    parent::preSave($event);
  }

  public function preInsert($event)
  {
    // cancellation ticket with member cards
    if ( $this->Price->member_card_linked
    && !( $this->printed || $this->integrated )
    && !is_null($this->cancelling) && is_null($this->duplicating) )
    {
      if ( !isset($models) )
        $models = Doctrine::getTable('MemberCardPriceModel')->createQuery('mcpm')
          ->andWhere('mcpm.price_id = ?',$this->price_id)
          ->andWhere('(mcpm.event_id IS NULL OR mcpm.event_id = ?)',$this->Manifestation->event_id)
          ->execute();
      
      foreach ( $models as $model )
      if ( $this->MemberCard->member_card_type_id == $model->member_card_type_id )
      {
        $mcp = new MemberCardPrice;
        $mcp->price_id = $this->price_id;
        $mcp->member_card_id = $this->MemberCard->id;
        
        if ( $model->event_id == $this->Manifestation->event_id )
          $mcp->event_id = $model->event_id;
        
        $mcp->save();
        break;
      }
    }
    
    // resetting generic properties
    $this->updated_at = NULL;
    $this->created_at = NULL;
    if ( sfContext::hasInstance() && sfContext::getInstance()->getUser()->getId() ) // especially necessary for online sales
      $this->sf_guard_user_id = NULL;
    
    parent::preInsert($event);
  }
  
  public function preUpdate($event)
  {
    $mods = $this->getModified();
    
    // only for normal tickets w/ member cards
    if ( $this->Price->member_card_linked
    && ( isset($mods['printed']) || isset($mods['integrated']) )
    && ( $this->printed || $this->integrated )
    && is_null($this->cancelling) && $this->Duplicatas->count() == 0 )
    {
      $q = Doctrine::getTable('MemberCard')->createQuery('mc')
        ->leftJoin('mc.Contact c')
        ->leftJoin('c.Transactions t')
        ->leftJoin('mc.MemberCardPrices mcp')
        ->leftJoin('mcp.Event e')
        ->leftJoin('e.Manifestations m')
        ->andWhere('t.id = ?',$this->transaction_id)
        ->andWhere('mc.created_at <= ?',date('Y-m-d H:i:s'))
        ->andWhere('mc.expire_at >  ?',date('Y-m-d H:i:s'))
        ->andWhere('mc.active = true')
        ->andWhere('mcp.price_id = ?',$this->price_id)
        ->andWhere('(mcp.event_id IS NULL OR m.id = ?)',$this->manifestation_id)
        ->orderBy('mcp.event_id IS NULL');
      $card = $q->fetchOne();
      
      if ( $card && $card->MemberCardPrices->count() > 0 )
      {
        $card->MemberCardPrices[0]->delete();
        $this->member_card_id = $card->id;
      }
      else
      {
        $this->printed = false;
        throw new liEvenementException("No more ticket left on the contact's member card");
      }
    }
    
    parent::preUpdate($event);
  }
  
  public function hasBeenCancelled($direction = 'both')
  {
    if ( $this->Cancelling->count() > 0 )
      return true;
    
    if ( in_array($direction,array('both','down')) )
    foreach ( $this->Duplicatas as $dup )
    if ( $dup->hasBeenCancelled('down') )
      return true;
    
    if ( in_array($direction,array('both','up')) )
    if ( !is_null($this->duplicating) )
    if ( $this->Duplicated->hasBeenCancelled('up') )
      return true;
    
    return false;
  }
  
  public function getOriginal()
  {
    if ( !$this->Duplicated )
      return $this;
    
    return $this->Duplicated->getOriginal();
  }
  
  public function getBarcode($salt = '')
  {
    return md5('#'.$this->id.'-'.$salt);
  }
  
  public function getIdBarcoded()
  {
    $c = ''.$this->id;
    $n = strlen($c);
    for ( $i = 12-$n ; $i > 0 ; $i-- )
      $c = '0'.$c;
    return $c;
  }
  
  public function __toString()
  {
    return '#'.$this->id;
  }
}

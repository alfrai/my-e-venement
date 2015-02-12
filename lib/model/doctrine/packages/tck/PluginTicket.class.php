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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * PluginTicket
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginTicket extends BaseTicket
{
  public function preSave($event)
  {
    $is_auth = sfContext::hasInstance() && sfContext::getInstance()->getUser()->getGuardUser();
    
    // the gauge, through the manifestation + the seat
    if ( !$this->gauge_id && $this->manifestation_id && $this->seat_id )
    {
      $q = Doctrine::getTable('Gauge')->createQuery('g', false)
        ->andWhereIn('g.workspace_id', $this->Seat->SeatedPlan->Workspaces->getPrimaryKeys())
        ->andWhere('g.manifestation_id = ?', $this->manifestation_id)
        ->leftJoin('g.Workspace ws');
      if ( sfContext::hasInstance() && method_exists(sfContext::getInstance()->getUser(), 'getId') )
        $q->leftJoin('ws.Order wso WITH wso.sf_guard_user_id = ?', sfContext::getInstance()->getUser()->getId())
          ->orderBy('wso.rank, g.id');
      else
        $q->orderBy('g.id');
      
      $this->Gauge = $q->fetchOne();
    }
    
    // the prices
    if ( (is_null($this->value) || is_null($this->price_id))
      && (!is_null($this->price_name) || !is_null($this->price_id))
      && !is_null($this->gauge_id) )
    {
      $q = Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.PriceManifestations pm')
        ->leftJoin('pm.Manifestation mpm')
        ->leftJoin('mpm.Gauges gpm')
        ->leftJoin('p.PriceGauges pg')
        ->leftJoin('pg.Gauge gpg')
        ->leftJoin('gpg.Manifestation m')
        ->andWhere('(gpm.id = ? OR gpg.id = ?)', array($this->gauge_id, $this->gauge_id))
        ->orderBy('pm.value DESC, pg.value DESC, pt.name')
      ;
      
      if ( is_null($this->price_id) )
        $q
          ->leftJoin('pm.Price pmp')
          ->leftJoin('pmp.Translation pmpt WITH pmpt.name = ?',$this->price_name)
          ->leftJoin('pg.Price pgp')
          ->leftJoin('pgp.Translation pgpt WITH pgpt.name = ?',$this->price_name)
          ->andWhere('(pmpt.id IS NOT NULL OR pgpt.id IS NOT NULL)')
        ;
      else
        $q
          ->leftJoin('pm.Price pmp WITH pmp.id = ?',$this->price_id)
          ->leftJoin('pg.Price pgp WITH pgp.id = ?',$this->price_id)
          ->andWhere('(pmp.id IS NOT NULL OR pgp.id IS NOT NULL)')
        ;
      
      if ( $price = $q->fetchOne() )
      {
        if ( is_null($this->price_name) )
          $this->price_name = $price->name;
        if ( is_null($this->price_id) )
          $this->price_id = $price->id;
        
        // always gives priority to PriceGauge, then PriceManifestation
        if ( is_null($this->value) )
          $this->value    = $price->PriceGauges->count() > 0
            ? $price->PriceGauges[0]->value
            : $price->PriceManifestations[0]->value;
      }
    }
    
    if ( !$this->price_name )
      $this->price_name = $this->Price->name;
    
    if ( $this->price_id && $is_auth )
    if ( !$this->Price->isAccessibleBy(sfContext::getInstance()->getUser()) )
      throw new liEvenementException('You tried to save a ticket with a price that you cannot access (user: #'.sfContext::getInstance()->getUser()->getId().', price: #'.$this->price_id.')');
    
    // the transaction's last update
    if ( $this->isModified() )
      $this->Transaction->updated_at = NULL;
    
    // get back the manifestation_id if not already set
    if ( !$this->manifestation_id && $this->gauge_id )
    {
      $this->Manifestation = Doctrine::getTable('Manifestation')->createQuery('m',true)
        ->leftJoin('m.Gauges g')
        ->andWhere('g.id = ?',$this->gauge_id)
        ->fetchOne();
    }
    
    // the holds: we can book a seated ticket within a hold only if its transaction is a HoldTransaction
    if ( $this->seat_id && $this->Seat instanceof Seat
      && ($hold = $this->Seat->isHeldFor($this->Manifestation))
      && !( $hold instanceof Hold && in_array($this->transaction_id, $tmp = $hold->HoldTransactions->toKeyValueArray('id', 'transaction_id')) )
    )
      $this->seat_id = NULL;
    
    // VAT resetting if the ticket is updated for a printing or an integration
    $mods = $this->getModified();
    if ( ( isset($mods['printed_at']) || isset($mods['integrated_at']) )
      && ( $this->printed_at || $this->integrated_at )
      && is_null($this->cancelling) && is_null($this->duplicating) && $this->Duplicatas->count() == 0
    )
      $this->vat = -1;
    
    // Setting the VAT to the ticket's Manifestation if not set
    if ( is_null($this->vat) || $this->vat == -1 )
      $this->vat = $this->Manifestation->Vat->value;
    
    // last chance to set taxes
    if ( !$this->printed_at || isset($mods['printed_at']) || isset($mods['integrated_at']) ) // if the ticket is being printed or is not printed
    {
      $this->taxes = 0;
      $taxes = new Doctrine_Collection('Tax');
      if ( $is_auth )
        $taxes->merge(sfContext::getInstance()->getUser()->getGuardUser()->Taxes);
      $taxes->merge($this->Manifestation->Taxes);
      if ( $this->price_id )
        $taxes->merge(is_object($this->Price) ? $this->Price->Taxes : Doctrine::getTable('Price')->find($this->price_id)->Taxes);
      $this->addTaxes($taxes);
    }
    
    // the generates a barcode (if necessary) to record in DB
    $this->qrcode;
    
    parent::preSave($event);
  }
  
  protected function addTaxes(Doctrine_Collection $taxes)
  {
    // taxes calculation (always after VAT calculation)
    foreach ( $taxes as $tax )
    {
      $val = 0;
      switch ( $tax->type ){
      case 'value':
        $this->taxes += $val = $this->value > 0 ? $tax->value : -$tax->value; // for cancelling tickets
        break;
      case 'percentage':
        $this->taxes += $val = round($this->value * $tax->value/100,2);
        break;
      }
    }
    return $this;
  }
  
  public function preInsert($event)
  {
    // cancellation ticket with member cards
    if ( $this->Price->member_card_linked
    && !( $this->printed_at || $this->integrated_at )
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
    
    // cancelling a seated ticket
    if ( !is_null($this->cancelling) && $this->seat_id )
    {
      $this->seat_id = NULL;
      $this->Cancelled->seat_id = NULL;
      $this->Cancelled->save();
    }
    
    // if the ticket's manifestation depends on an other one (and the ticket is not a cancellation nor a duplication
    if ( !is_null($this->Manifestation->depends_on)
      && is_null($this->cancelling) && is_null($this->duplicating) )
    {
      $ticket = new Ticket;
      $ticket->gauge_id = Doctrine_Query::create()->from('Gauge g')
        ->andWhere('g.workspace_id IN (SELECT gg.workspace_id FROM Gauge gg WHERE gg.id = ?)',$this->gauge_id)
        ->andWhere('g.manifestation_id = ?',$this->Manifestation->depends_on)
        ->fetchOne()->id;
      $ticket->price_name = $this->price_name;
      $ticket->transaction_id = $this->transaction_id;
      $ticket->sf_guard_user_id = $this->sf_guard_user_id;
      $ticket->save();
      $this->Transaction->Tickets[] = $ticket;
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
    if ( $this->price_id && is_object($this->Price) && $this->Price->member_card_linked
    && ( isset($mods['printed_at']) || isset($mods['integrated_at']) )
    && ( $this->printed_at || $this->integrated_at )
    && is_null($this->cancelling) && is_null($this->duplicating) && $this->Duplicatas->count() == 0 )
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
        $this->printed_at = NULL;
        throw new liEvenementException("No more ticket left on the contact's member card");
      }
    }
    
    parent::preUpdate($event);
  }
  
  public function getNumerotation()
  {
    return $this->Seat->name;
  }

  public function setNumerotation($str = NULL)
  {
    if ( is_null($str) )
    {
      $this->seat_id = NULL;
      return $this;
    }
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->leftJoin('s.SeatedPlan sp')
      ->leftJoin('sp.Location l')
      ->leftJoin('l.Manifestations m')
      ->leftJoin('sp.Workspaces spw')
      ->leftJoin('spw.Gauges g')
      ->andWhere('g.id = ?', $this->gauge_id)
      ->andWhere('g.manifestation_id = m.id')
      ->andWhere('s.name = ?', $str)
    ;
    $this->Seat = $q->fetchOne();
    
    return $this;
  }
  
  public function addLinkedProducts()
  {
    if ( !sfContext::hasInstance() )
      return;
    $sf_user = sfContext::getInstance()->getUser();
    
    // already bought products
    $pdts = array();
    foreach ( $this->BoughtProducts as $bp )
      $pdts[] = $bp->Declination->product_id;
    
    $collection = array('LinkedProducts' => array());
    foreach ( array($this->Manifestation, $this->Price, $this->Gauge->Workspace, $this->Manifestation->Event->MetaEvent) as $object )
    {
      if ( $object->getTable()->hasRelation($rel = 'LinkedProducts') )
      {
        $links = $object->$rel->getData();
        foreach ( $links as $link )
        if ( !in_array($link->id, $pdts) ) // no duplication
        {
          if ( in_array($link->id, $collection[$rel]) )
            continue;
          $collection[$rel][] = $link->id;
          if (!( $link instanceof liUserAccessInterface && !$link->isAccessibleBy($sf_user) ))
          {
            $max_price = $link->getMostExpansivePrice($sf_user);
            if ( $max_price['price'] && $link->Declinations->count() > 0 )
            {
              $bp = new BoughtProduct;
              $declination = false;
              foreach ( $link->Declinations as $declination )
              if ( $declination->prioritary )
              {
                $bp->Declination = $declination;
                $bp->Price = $max_price['price']->Price;
                $bp->Transaction = $this->Transaction;
                $this->BoughtProducts[] = $bp;
                break;
              }
            }
          }
        }
      }
    }
    
    return $this;
  }
  
  public function isSold()
  {
    return !(is_null($this->printed_at) && is_null($this->cancelling) && is_null($this->integrated_at));
  }
  public function isDuplicata()
  {
    return !is_null($this->duplicating);
  }

  public function getIndexesPrefix()
  {
    return strtolower(get_class($this));
  }
}

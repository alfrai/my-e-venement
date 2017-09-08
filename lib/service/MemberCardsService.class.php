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
*    Copyright (c) 2006-2017 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/

class MemberCardsService extends EvenementService
{
  public function completeMemberCardsWithMCPrice(array $values, Event $event = NULL)
  {
    $mcs = $this->getActiveMemberCards($values['member_card_type_id']);
    
    foreach ( $mcs as $mc ) {
        $mcp = new MemberCardPrice;
        $mcp->price_id = $values['price_id'];
        
        // anonymous MemberCardPrice
        if ( !isset($event) ) {
            $this->addMemberCardPriceToMemberCard($mcp, $mc, $values['quantity']);
            continue;
        }
        
        // if there is already a MemberCardPrice for this event_id, do not process
        if ( in_array($event->id, $mc->MemberCardPrices->toKeyValueArray('id', 'event_id')) ) {
            continue;
        }
        
        // if there is already a ticket pointing on the given $event linked to the current MemberCard, do not process
        if ( !$this->hasMemberCardTicketLinkedToEvent($mc, $event) ) {
            $mcp->Event = $event;
            $this->addMemberCardPriceToMemberCard($mcp, $mc, $values['quantity']);
        }
    }
    
    return $mcs;
  }
  
  private function hasMemberCardTicketLinkedToEvent(MemberCard $mc, Event $event)
  {
     foreach ( $mc->Tickets as $ticket ) {
        if ( $ticket->Manifestation->event_id == $event_id ) {
            return true;
        }
    }
    return false;
  }
  
  private function addMemberCardPriceToMemberCard(MemberCardPrice $mcp, MemberCard $mc, $quantity = 1)
  {
    $mcps = new Doctrine_Collection('MemberCardPrice');
    
    // nothing to add
    if ( $quantity == 0 ) {
        return $mcps;
    }
    
    // infinite quantity cases
    if ( $quantity < 0 ) {
        $quantity = 1;
    }
    
    // action !
    for ( $i = 0 ; $i < $quantity ; $i++ ) {
        $tmp = $mcp->copy();
        $tmp->MemberCard = $mc;
        $tmp->save();
        $mcps[] = $tmp;
    }
    
    return $mcps;
  }
  
  public function getActiveMemberCards($type_id = NULL)
  {
    $q = $this->createQueryForActiveMemberCards($type_id);
    return $q->execute();
  }
  
  public function getActiveMemberCardsForEvent(Event $event, $type_id = NULL)
  {
    $q = $this->createQueryForActiveMemberCards($type_id)
        ->leftJoin('mc.MemberCardPrices mcp')
        ->andWhere('mcp.event_id = ?', $event->id)
    ;
    return $q->execute();
  }
  
  /**
   * This function returns available MemberCardPrices for a Transaction and optionaly a Manifestation
   *
   * @param $transaction    Transaction
   * @param $manifestation  Manifestation
   * @return Doctrine_Collection('MemberCardPrice')
   * @throw liEvenementException
   */
  public function getAvailableMCPrices(Transaction $transaction, Manifestation $manifestation = NULL)
  {
    $mcp = array();
    try {

    $mcs = $transaction->contact_id
      ? $transaction->getContact()->MemberCards
      : $transaction()->MemberCards;
    if ( $mcs->count() == 0 )
      return $mcp;
    
    // get back available prices
    foreach ( $mcs as $mc )
    if ( $mc->active || $mc->transaction_id == $transaction->id )
    foreach ( $mc->MemberCardPrices as $price )
    {
      $event_id = is_null($price->event_id) ? '' : $price->event_id;
      
      if ( $event_id && $manifestation instanceof Manifestation && $price->event_id != $manifestation->event_id )
        continue;
      
      if ( !isset($mcp[$price->price_id][$event_id]) )
        $mcp[$price->price_id][$event_id] = 0;
      
      $mcp[$price->price_id][$event_id]++;
    }
    
    // get back already booked tickets
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->select('tck.*, m.event_id AS event_id')
      ->andWhere('tck.printed_at IS NULL')
      ->andWhere('tck.member_card_id IS NOT NULL OR t.id = ?', $transaction->id)
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.Manifestations em')
      ->leftJoin('tck.Price p')
      ->andWhere('p.member_card_linked = ?',true)
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.contact_id = ?',$transaction->Contact->id)
      ->leftJoin('t.Order o')
    ;
    if ( $manifestation )
      $q->andWhere('em.id = ?', $manifestation->id)
        ->andWhere('o.id IS NOT NULL OR t.id = ? AND tck.manifestation_id != em.id', $transaction->id)
      ;
    else
      $q->andWhere('o.id IS NOT NULL OR t.id = ?', $transaction->id);
      
    $tickets_to_count = $q->execute();
    foreach ( $tickets_to_count as $ticket )
    {
      if ( isset($mcp[$ticket->price_id][$ticket->event_id]) )
        $mcp[$ticket->price_id][$ticket->event_id]--;
      elseif ( isset($mpc[$ticket->price_id]['']) )
        $mcp[$ticket->price_id]['']--;
    }
    
    return $mcp;
    
    }
    catch ( liEvenementException $e )
    { return $mcp; }
  }

  private function createQueryForActiveMemberCards($type_id = NULL)
  {
    $q = Doctrine::getTable('MemberCard')->retreiveListOfActivatedCards()
        ->andWhere('mc.expire_at > NOW()')
        ->select('mc.*')
    ;
    
    if ( isset($type_id) ) {
        $q->andWhere('mc.member_card_type_id = ?', $type_id);
    }
    
    return $q;
  }
}

<?php

/**
 * MemberCardSeatingService
 *
 * @author Baptiste LARVOL-SIMON <baptiste.larvol.simon@libre-informatique.fr>
 */
class MemberCardSeatingService
{
    /**
     * Function seatMemberCard
     *
     * @param $mc       MemberCard      The member card to process
     * @param $manif    Manifestation   The manifestation where to book a seat
     * @param $transac  Transaction     (optional) An existing Transaction where to add tickets
     * @return          Ticket
     * @throws          liEvenementException
     */
    public function seatMemberCard(MemberCard $mc, Manifestation $manif, Transaction $transac = NULL)
    {
        $seat = $this->getSeatInGaugeFor($mc, $manif);
        $price = $this->getPriceFor($mc, $manif);
        
        try {
            $ticket = $this->createTicket($seat, $price, $transac);
            $this->completeTicket($mc, $ticket);
            $ticket->save();
        }
        catch ( Doctrine_Connection_Exception $e ) {
            $this->log($e);
        }
        
        return $ticket;
    }
    
    private function log(Exception $e)
    {
        if ( sfConfig::get('sf_web_debug') ) {
            error_log($e->getMessage());
        }
    }
    
    private function completeTicket(MemberCard $mc, Ticket $ticket)
    {
        $ticket->MemberCard = $mc;
        $ticket->Transaction->contact_id = $mc->contact_id;
        $ticket->Transaction->Order[0]; // creates a new Order implicitly if no Order exists yet
        
        return $ticket;
    }
    
    private function getPriceFor(MemberCard $mc, Manifestation $manif)
    {
        foreach ( $mc->MemberCardPrices as $mcp ) {
            if ( $mcp->event_id == $manif->event_id ) {
                return $mcp->Price;
            }
        }
        
        throw new liException(sprintf('No price available for Manifestation #%d in MemberCard #%d.', $manif->id, $mc->id));
    }
    
    private function createTicket(Seat $seat, Price $price, Transaction $transac = NULL)
    {
        $ticket = new Ticket;
        
        $ticket->Seat = $seat;
        $ticket->gauge_id = $seat->gauge_id;
        $ticket->Price = $price;
        $ticket->Transaction = $transac instanceof Transaction ? $transac : new Transaction;
        
        return $ticket;
    }
    
    private function getSeatInGaugeFor(MemberCard $mc, Manifestation $manif)
    {
        $seat_name = $mc->privileged_seat_name;
        
        $q = Doctrine::getTable('Seat')->createQuery('s')
            ->select('s.*, sp.id, ws.id, g.id, g.id AS gauge_id')
            
            ->andWhere('s.name = ?', $seat_name)
            
            ->leftJoin('s.SeatedPlan sp')
            ->leftJoin('sp.Workspaces ws')
            ->leftJoin('sp.Location l')
            ->leftJoin('l.Manifestations m')
            ->leftJoin('ws.Gauges g WITH g.manifestation_id = m.id')
            ->andWhere('g.manifestation_id = ?', $manif->id)
            
            ->leftJoin('g.Tickets tck WITH tck.seat_id = s.id')
            ->andWhere('tck.id IS NULL')
        ;
        $seat = $q->fetchOne();
        
        if ( !$seat ) {
            throw new liEvenementException(sprintf('No seat available for seat name "%s"', $seat_name));
        }
        
        return $seat;
    }
}

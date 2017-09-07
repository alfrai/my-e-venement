<?php

/**
 * MemberCardsSeatingService
 *
 * @author Baptiste LARVOL-SIMON <baptiste.larvol.simon@libre-informatique.fr>
 */
class MemberCardsSeatingService
{
    /**
     * @var MemberCardSeatingService
     */
    private $memberCardSeatingService;
    
    public function setMemberCardSeatingService(MemberCardSeatingService $service)
    {
        $this->memberCardSeatingService = $service;
        return $this;
    }
    
    /**
     * Function seatManyMemberCardForAllManifestation  seats MemberCards on the all possible Manifestations
     *
     * @param $mc       Doctrine_Collection('MemberCard')      The MemberCards to process
     * @return          Doctrine_Collection('Ticket')
     * @throws          liEvenementException
     */
    public function seatManyMemberCardForAllManifestation(Doctrine_Collection $mcs)
    {
        $tickets = new Doctrine_Collection('Ticket');
        
        foreach ( $mcs as $mc ) {
            $new_tickets = $this->seatOneMemberCardForAllManifestation($mc);
            $tickets->merge($new_tickets);
        }
        
        return $tickets;
    }

    /**
     * Function seatOneMemberCardForAllManifestation  seats MemberCard on the all possible Manifestations
     *
     * @param $mc       MemberCard      The MemberCard to process
     * @return          Doctrine_Collection('Ticket')
     * @throws          liEvenementException
     */
    public function seatOneMemberCardForAllManifestation(MemberCard $mc)
    {
        $mcps = $this->getMCPsWithEvent($mc);
        $tickets = $this->seatMemberCardPrices($mcps);
        
        return $tickets;
    }
    
    /**
     * Function seatManyMemberCardForOneManifestation  seats MemberCards on the given Manifestation
     *
     * @param $mc       Doctrine_Collection('MemberCard')      The MemberCards to process
     * @param $manif    Manifestation   The targetted Manifestation
     * @return          Doctrine_Collection('Ticket')
     * @throws          liEvenementException
     */
    public function seatManyMemberCardForOneManifestation(Doctrine_Collection $mcs, Manifestation $manif)
    {
        $tickets = new Doctrine_Collection('Ticket');
        
        foreach ( $mcs as $mc ) {
            $tickets[] = $this->seatOneMemberCardForOneManifestation($mc, $manif);
            $tickets->merge($new_tickets);
        }
        
        return $tickets;
    }
    
    /**
     * Function seatOneMemberCardForOneManifestation  seats MemberCard on the given Manifestation
     *
     * @param $mc       MemberCard      The MemberCard to process
     * @param $manif    Manifestation   The targetted Manifestation
     * @return          Doctrine_Collection('Ticket')
     * @throws          liEvenementException
     */
    public function seatOneMemberCardForOneManifestation(MemberCard $mc, Manifestation $manif)
    {
        $tickets = new Doctrine_Collection('Ticket');
        $tickets[] = $this->memberCardSeatingService->seatMemberCard($mc, $manif);
        return $tickets;
    }
    
    private function seatMemberCardPrices(Doctrine_Collection $mcps)
    {
        $tickets = new Doctrine_Collection('Ticket');
        
        foreach ( $mcps as $mcp ) {
            $ticket = $this->seatMemberCardPrice($mcp);
            if ( isset($ticket) ) {
                $tickets[] = $ticket;
            }
        }
        
        return $tickets;
    }
    
    private function seatMemberCardPrice($mcp, Manifestation $manifestation = NULL)
    {
        foreach ( $mcp->Event->Manifestations as $manif ) {
            if ( !$this->testIfManifestationIsWalkable($manif, $manifestation) ) {
                continue;
            }

            try {
                return $this->memberCardSeatingService->seatMemberCard($mcp->MemberCard, $manif);
            }
            catch ( liEvenementException $e ) {
                return NULL;
            }
        }
        
        return NULL;
    }
    
    private function testIfManifestationIsWalkable(Manifestation $manif, Manifestation $target = NULL)
    {
        if ( !isset($target) ) {
            return true;
        }
        
        if ( $manif->id == $target->id ) {
            return true;
        }
        
        return false;
    }
    
    private function getMCPsWithEvent(MemberCard $mc)
    {
        $mcps = new Doctrine_Collection('MemberCardPrice');
        
        foreach ( $mc->MemberCardPrices as $mcp ) {
            if ( $mcp->event_id !== NULL ) {
                $mcps[] = $mcp;
            }
        }
        
        return $mcps;
    }
}

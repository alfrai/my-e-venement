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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

class Seater
{
  protected $seats, $query, $kept, $done, $hold, $gauge_id = 0;
  
  public function __construct($gauge_id = NULL, Hold $hold = NULL)
  {
    if ( is_null($gauge_id) && is_null($hold) )
      throw new liEvenementException('Cannot build a Seater without any hold or gauge given...');
    
    $this->kept = new Doctrine_Collection('Seat');
    $this->done = new Doctrine_Collection('Seat');
    $this->gauge_id = $gauge_id;
    $this->hold = $hold;
    $this->seats = $this->createQuery()->execute();
  }
  
  public function createQuery($alias = 's')
  {
    $q = Doctrine::getTable('Seat')->createQuery($alias)
      ->select("$alias.*, n.*")
      ->leftJoin("$alias.SeatedPlan sp")
      ->leftJoin('sp.Workspaces spw')
      ->leftJoin('spw.Gauge g')
      ->leftJoin('g.Manifestation m')
      
      ->leftJoin("$alias.Tickets tck WITH tck.manifestation_id = m.id")
      ->andWhere('tck.id IS NULL')

      ->leftJoin("$alias.Neighbors n")
      
      ->orderBy("$alias.rank, $alias.name")
    ;
    
    // Holds
    $q->leftJoin("$alias.Holds h WITH h.manifestation_id = g.manifestation_id");
    if ( $this->hold instanceof Hold )
      $q->andWhere('h.id = ?', $this->hold->id);
    else
      $q->andWhere('h.id IS NULL');
    
    // Gauge
    if ( $this->gauge_id )
      $q->andWhere('g.id = ?', $this->gauge_id);
    else
      $q->andWhere('g.manifestation_id = h.manifestation_id');
    
    return $q;
  }
  
  /**
    * Add a seat in the list of free seats
    * @param $seat    Seat
    * @return         Seater $this
    *
    **/
  public function addSeat(Seat $seat)
  {
    $this->seats[] = $seat;
    return $this;
  }
  
  /**
    * Organize a list that we can avoid all orphans we can
    * @param $seats     Doctrine_Collection of Seat elements to order
    * @return           Doctrine_Collection of ordered Seat elements
    *
    **/
  public function organizeList(Doctrine_Collection $seats)
  {
    if ( !is_a($seats->getTable()->getComponentName(), 'Seat', true) )
      throw new liSeatedException('A collection of Seats was excepted, found: '.$seats->getTable()->getComponentName());
    
    $by_rank = array();
    foreach ( $seats as $seat )
      $by_rank[$seat->rank.' - '.$seat->id] = $seat;
    ksort($by_rank);
    
    $r = new Doctrine_Collection('Seat');
    foreach ( $by_rank as $seat )
    foreach ( $seat->Neighbors as $n )
    {
      if ( in_array($n->id, $seats->getPrimaryKeys()) && in_array($n->id, $this->seats->getPrimaryKeys()) )
      {
        if ( !in_array($seat->id, $r->getPrimaryKeys()) )
          $r[$seat->id] = $seat;
        $s = $seats[array_search($n->id, $seats->getPrimaryKeys())];
        $r[$s->id] = $s;
      }
    }
    
    if ( $r->count() != $seats->count() )
    foreach ( $seats as $seat )
    if ( !in_array($seat->id, $r->getPrimaryKeys()) )
      $r[$seat->id] = $seat;
    
    return $r;
  }
  
  /**
    * Find seats for $qty tickets
    * @param $qty     integer             how many seats do you need
    * @param $exclude Doctrine_Collection if you want to avoid looking for seats in that direction... Doctrine_Collection with the association Seat->id => Seat
    * @return         Doctrine_Collection the Seats we have found
    *
    **/
  public function findSeats($qty, Doctrine_Collection $exclude = NULL)
  {
    $this->done = new Doctrine_Collection('Seat');
    if ( $exclude instanceof Doctrine_Collection )
      $this->done->merge($exclude);
    
    $this->kept = new Doctrine_Collection('Seat');
    foreach ( $this->seats as $seat )
    {
      $this->_findSeatsWalk($seat);
      if ( $this->kept->count() >= intval($qty) )
        break;
      $this->kept = new Doctrine_Collection('Seat');
    }
    
    $i = 0;
    foreach ( $this->kept as $key => $seat )
    {
      if ( $i >= intval($qty) )
        unset($this->kept[$key]);
      $i++;
    }
    
    return $this->kept;
  }
  
  /**
    * Find orphans that can be generated by an action...
    * @param $seats   Doctrine_Collection|string       a Doctrine_Collection representing seats to book, or the name of a single seat, or nothing to look for every orphans in the venue
    * @return         Doctrine_Collection              detected orphans
    *
    **/
  public function findOrphansWith($seats = NULL)
  {
    $orphans = new Doctrine_Collection(Doctrine::getTable('Seat'));
    
    $this->kept = new Doctrine_Collection('Seat');
    if ( is_string($seats) )
    {
      foreach ( $this->seats as $key => $seat )
      if ( $seat->name == $seats )
      {
        $this->kept[$seat->id] = $seat;
        break;
      }
      $seats = new Doctrine_Collection(Doctrine::getTable('Seat'));
    }
    elseif ( $seats instanceof Doctrine_Collection )
      $this->kept->merge($seats);
    else
      throw new liSeatedException('If you want to look for orphans, you have to tell correctly the Seater what to look for.');
    
    foreach ( $this->kept as $seat )
    if ( $seat )
      $orphans->merge($this->_findOrphansWithWalk($seat));
    
    return $orphans;
  }
  
  /**
    * Find seats for $qty tickets being sure it does not generate any orphans, or it generates orphans because there are no better solution
    * @param $qty       integer                     how many seats do you need
    * @param $excluded  Doctrine_Collection         if you want to avoid looking for seats in that direction... Doctrine_Collection with the association Seat->id => Seat
    * @return           Doctrine_Collection|FALSE   the Seats we have found OR FALSE if it failed to find seats w/o any orphan
    *
    **/
  public function findSeatsExcludingOrphans($qty, Doctrine_Collection $excluded = NULL)
  {
    $seats = new Doctrine_Collection('Seat');
    if (! $excluded instanceof Doctrine_Collection )
      $excluded = new Doctrine_Collection('Seat');
    
    for ( $i = 0 ; $this->findOrphansWith($seats)->count() > 0 || $seats->count() < $qty ; $i++ )
    {
      $seats = $this->findSeats($qty, $excluded); // find seats, excluding the past tries
      $excluded->merge($seats); // add this batch of seats to exclude seats
      
      if ( $seats === false || $i > 500 ) // 500 is a protection against infinite loops
        return false;
    }
    
    return $seats;
  }
  
  protected function _findOrphansWithWalk(Seat $seat)
  {
    $seats = new Doctrine_Collection('Seat');
    
    foreach ( $seat->Neighbors as $n )
    {
      // check if the seat can be an orphan, at least
      if ( in_array($n->id, $this->kept->getPrimaryKeys())      // if it's one of the selected seats
        || !in_array($n->id, $this->seats->getPrimaryKeys()) )  // if it's a seat that is already booked
        continue; // this neighbor is not a free seat, it cannot be an orphan
      
      $n = $this->seats[array_search($n->id, $this->seats->getPrimaryKeys())];
      $i = $n->Neighbors->count();
      foreach ( $n->Neighbors as $n2 )
      {
        if (  in_array($n2->id, $this->kept->getPrimaryKeys())     // if it's one of the selected seats
          || !in_array($n2->id, $this->seats->getPrimaryKeys()) )  // if it's a seat that is already booked
          $i--; // the neighbor of the neighbor is not a free seat, so the neighbor is an orphan on this side
      }
      
      if ( $n->Neighbors->count() > 1 && $i == 0 )
        $seats[] = $n;
    }
    
    return $seats;
  }
  
  protected function _findSeatsWalk(Seat $seat)
  {
    if ( in_array($seat->id, $this->done->getPrimaryKeys())
      || !in_array($seat->id, $this->seats->getPrimaryKeys()) )
      return;
    
    $this->done[$seat->id] = $seat;
    $this->kept[$seat->id] = $seat;
    
    // the other neighbors
    foreach ( $seat->Neighbors as $n )
      $this->_findSeatsWalk($this->seats[array_search($n->id, $this->seats->getPrimaryKeys())]);
  }
}

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

/**
 * seats actions.
 *
 * @package    e-venement
 * @subpackage seats
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class seatsActions extends sfActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }

 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward404Unless($request->getParameter('gauge_id'));

    // basic data
    $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
      ->leftJoin('sp.Seats s')
      ->orderBy('s.name')
      ->andWhere('sp.id = ?', $request->getParameter('id'))
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Gauges g')
      ->leftJoin('g.Manifestation m')
      ->andWhere('sp.location_id = m.location_id')
      ->andWhere('g.id = ?', $request->getParameter('gauge_id',0))
    ;
    $this->seated_plan = $q->fetchOne();
    $this->forward404Unless($this->seated_plan);
    
    // specific data
    $this->occupied = array();
    $this->transaction = $this->getUser()->getTransaction();
    sfConfig::set('sf_escaping_strategy', false);
    
    if ( ($gid = intval($request->getParameter('gauge_id', 0))) > 0 )
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->select('tck.*, t.*, c.*, pro.*, org.*, o.*, pc.*')
        ->leftJoin('tck.Seat s')
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Contact c')
        ->leftJoin('t.Professional pro')
        ->leftJoin('pro.Organism org')
        ->leftJoin('pro.Contact pc')
        ->leftJoin('t.Order o')
        ->leftJoin('s.SeatedPlan sp')
        ->andWhere('tck.seat_id IS NOT NULL')
        ->andWhere('sp.id = ?', $request->getParameter('id'))
        ->leftJoin('tck.Manifestation m')
        ->leftJoin('m.Gauge g')
        ->andWhere('g.id = ?', $request->getParameter('gauge_id'))
      ;
      
      foreach ( $q->execute() as $ticket )
        $this->occupied[$ticket->seat_id] = array(
          'type'            => 'ordered'.($ticket->transaction_id === $this->transaction->id ? ' in-progress' : ''),
          'transaction_id'  => $ticket->gauge_id == $gid && $ticket->transaction_id === $this->transaction->id ? '#'.$this->transaction->id : false,
          'ticket_id'       => $ticket->id,
          'price_id'        => $ticket->price_id,
          'gauge_id'        => $ticket->gauge_id,
          //'spectator'      => $ticket->Transaction->professional_id ? $ticket->Transaction->Professional->Contact.' '.$ticket->Transaction->Professional : (string)$ticket->Transaction->Contact,
        );
    }
  }
}

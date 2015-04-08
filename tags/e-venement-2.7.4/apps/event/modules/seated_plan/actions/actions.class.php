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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/seated_planGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/seated_planGeneratorHelper.class.php';

/**
 * seated_plan actions.
 *
 * @package    e-venement
 * @subpackage seated_plan
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class seated_planActions extends autoSeated_planActions
{
  public function executeGetSeats(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->occupied = array();
    $this->transaction_id = intval($request->getParameter('transaction_id', 0));
    sfConfig::set('sf_escaping_strategy', false);
    
    if ( $this->getUser()->hasCredential('tck-seat-allocation')
      && intval($request->getParameter('gauge_id', 0)) > 0 )
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->select('tck.*, t.*, c.*, pro.*, org.*, o.*, pc.*')
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Contact c')
        ->leftJoin('t.Professional pro')
        ->leftJoin('pro.Organism org')
        ->leftJoin('pro.Contact pc')
        ->leftJoin('t.Order o')
        ->leftJoin('tck.Gauge g')
        ->leftJoin('g.Workspace ws')
        ->leftJoin('ws.SeatedPlans sp')
        ->leftJoin('sp.Workspaces spws')
        ->leftJoin('spws.Gauges spwsg')
        ->leftJoin('g.Manifestation m')
        ->leftJoin('tck.Cancelling cancel')
        ->andWhere('tck.cancelling IS NULL')
        ->andWhere('duplicatas.id IS NULL AND cancel.id IS NULL')
        ->andWhere('tck.numerotation IS NOT NULL AND tck.numerotation != ?','')
        ->andWhere('spwsg.id = ? AND spwsg.manifestation_id = g.manifestation_id', $request->getParameter('gauge_id')) // a trick to get all tickets from all related gauge
        ->andWhere('sp.id = ?', $request->getParameter('id'))
        ->andWhere('m.location_id = ?', $this->seated_plan->location_id);
      
      foreach ( $q->execute() as $ticket )
        $this->occupied[$ticket->numerotation] = array(
          'type' => ($ticket->printed_at || $ticket->integrated_at ? 'printed' : ($ticket->Transaction->Order->count() > 0 ? 'ordered' : 'asked')).($ticket->transaction_id === $this->transaction_id ? ' in-progress' : ''),
          'transaction_id' => '#'.$ticket->transaction_id,
          'spectator'      => $ticket->Transaction->professional_id ? $ticket->Transaction->Professional->Contact.' '.$ticket->Transaction->Professional : (string)$ticket->Transaction->Contact,
        );
    }
  }
  
  public function executeSeatAdd(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array()) ))
      throw new liSeatingException('Given data do not permit the seat recording (no data).');
    if ( !isset($data['x']) || !isset($data['y']) || !isset($data['diameter']) || !isset($data['name']) || !intval($request->getParameter('id',0)) > 0 )
      throw new liSeatingException('Given data do not permit the seat recording (bad data).');
    
    $seat = new Seat;
    $seat->seated_plan_id = $request->getParameter('id');
    foreach ( array('name', 'x', 'y', 'diameter') as $fieldName )
      $seat->$fieldName = $data[$fieldName];
    $seat->save();
    
    return sfView::NONE;
  }
  
  public function executeSeatDel(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array()) ))
      throw new liSeatingException('Given data do not permit the seat deletion (no data).');
    if ( !isset($data['name']) || !intval($request->getParameter('id',0)) > 0 )
      throw new liSeatingException('Given data do not permit the seat deletion (bad data).');
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->andWhere('s.seated_plan_id = ?', $request->getParameter('id'))
      ->andWhere('s.name = ?', $data['name']);
    $q->delete()->execute();
    
    return sfView::NONE;
  }
  
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT s.picture_id FROM SeatedPlan s WHERE s.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return $this->redirect('seated_plan/edit?id='.$request->getParameter('id'));
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->executeEdit($request);
    if ( $request->getParameter('transaction_id',false) )
      $this->form->transaction_id = $request->getParameter('transaction_id',false);
  }
  public function executeEdit(sfWebRequest $request)
  {
    if ( $request->getParameter('id',false) )
    {
      $this->seated_plan = Doctrine::getTable('SeatedPlan')->createQuery('sp')
        ->andWhere('sp.id = ?',$request->getParameter('id'))
        ->leftJoin('sp.Seats s')
        ->orderBy('s.name')
        ->fetchOne();
    }
    else
    {
      // if only gauge_id is set
      $this->seated_plan = Doctrine::getTable('SeatedPlan')->createQuery('sp')
        ->leftJoin('sp.Seats s')
        ->leftJoin('sp.Workspaces ws')
        ->leftJoin('ws.Gauges g')
        ->leftJoin('g.Manifestation m')
        ->andWhere('sp.location_id = m.location_id')
        ->andWhere('g.id = ?', $request->getParameter('gauge_id',0))
        ->fetchOne();
    }
    
    $this->forward404Unless($this->seated_plan);
    $this->form = $this->configuration->getForm($this->seated_plan);
    
    if ( $request->getParameter('gauge_id',false) )
      $this->form->gauge_id = $request->getParameter('gauge_id');
  }
}

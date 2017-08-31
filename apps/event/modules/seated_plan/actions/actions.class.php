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

require_once __DIR__.'/../lib/seated_planGeneratorConfiguration.class.php';
require_once __DIR__.'/../lib/seated_planGeneratorHelper.class.php';

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
  public function executeDuplicate(sfWebRequest $request)
  {
    $this->executeShow($request);
    $sp = $this->seated_plan->copy();
    $sp->save();
    
    // copying the seats
    foreach ( $this->seated_plan->Seats as $seat )
    {
      $s = $seat->copy();
      $sp->Seats[$seat->name] = $s;
      $s->SeatedPlan = $sp;
      $s->save();
    }
    
    // creating the neighborhood
    $neighbors = array();
    foreach ( $this->seated_plan->Seats as $seat )
    foreach ( $seat->Neighbors as $neighbor )
    {
      if ( !in_array(array($seat->name, $neighbor->name), $neighbors)
        && !in_array(array($neighbor->name, $seat->name), $neighbors) )
      {
        $sl = new SeatLink;
        $sl->seat1 = $sp->Seats[$seat->name]->id;
        $sl->seat2 = $sp->Seats[$neighbor->name]->id;
        $sl->save();
        $neighbors[] = array($seat->name, $neighbor->name);
      }
    }
    
    $this->redirect('seated_plan/edit?id='.$sp->id);
  }
  
  public function executeBatchMerge(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    if ( count($ids = $request->getParameter('ids')) < 2 )
    {
      $this->getUser()->setFlash('notice', __('Please choose more than one record...'));
      return;
    }
    
    $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
      ->andWhereIn('sp.id', $ids)
    ;
    $picid = NULL;
    foreach ( $plans = $q->execute() as $plan )
    if ( is_null($picid) )
      $picid = $plan->picture_id;
    elseif ( $plan->picture_id != $picid )
    {
      $plan->Picture->delete();
      $plan->picture_id = $picid;
    }
    
    $this->getUser()->setFlash('notice', __('The selected plans are now sharing their pictures.'));
    $plans->save();
  }
  public function executeGetHoldSeats(sfWebRequest $request)
  {
    return require(__DIR__.'/get-hold-seats.php');
  }
  public function executeGetControls(sfWebRequest $request)
  {
    return require(__DIR__.'/get-controls.php');
  }
  public function executeGetDebts(sfWebRequest $request)
  {
    return require(__DIR__.'/get-debts.php');
  }
  public function executeGetRanks(sfWebRequest $request)
  {
    require(__DIR__.'/get-ranks.php');
  }
  public function executeGetZones(sfWebRequest $request)
  {
    require(__DIR__.'/get-zones.php');
  }
  public function executeSetZones(sfWebRequest $request)
  {
    require(__DIR__.'/set-zones.php');
  }
  public function executeClearZones(sfWebRequest $request)
  {
    require(__DIR__.'/clear-zones.php');
  }
  public function executeGetShortnames(sfWebRequest $request)
  {
    require(__DIR__.'/get-shortnames.php');
  }
  public function executeGetSeats(sfWebRequest $request)
  {
    return require(__DIR__.'/get-seats.php');
  }
  public function executeGetGroup(sfWebRequest $request)
  {
    return require(__DIR__.'/get-group.php');
  }
  public function executeGetTransaction(sfWebRequest $request)
  {
    return require(__DIR__.'/get-transaction.php');
  }
  
  // Seat links definition
  protected function getLinksParameters(sfWebRequest $request)
  {
    return $request->getParameter('auto_links', array());
  }
  protected function preLinks($request)
  {
    if ( intval($request->getParameter('id')).'' !== ''.$request->getParameter('id') )
      throw new liSeatedException('A correct seated plan id is needed');
  }
  public function executeLinksClear(sfWebRequest $request)
  {
    $params = $this->getLinksParameters($request);
    $this->preLinks($request);
    if ( !isset($params['clear']) )
      throw new liSeatedException('The provided informations for this action are not correct.');
    
    $this->getRoute()->getObject()->clearLinks();
    
    return sfView::NONE;
  }
  public function executeLinksBuild(sfWebRequest $request)
  {
    $params = $this->getLinksParameters($request);
    $this->preLinks($request);
    if ( !isset($params['format']) )
      throw new liSeatedException('The provided informations for this action are not correct.');
    
    $format = '/'.str_replace(array('%row%', '%rowm%', '%rown%', '%num%'), array('([a-zA-Z]+)', '(\w+)', '(\d+)', '([0-9]+)'), $params['format']).'/';
    $hop = isset($params['contiguous']) ? 1 : 2;
    
    if ( isset($params['additive']) )
      $this->getRoute()->getObject()->clearLinks();
      
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->andWhere('s.seated_plan_id = ?', $request->getParameter('id'))
      ->orderBy('s.name')
    ;
    
    $cpt = 0;
    $seats = array();
    foreach ( $q->execute() as $seat )
      $seats[$seat->name] = $seat;
    foreach ( $seats as $num => $seat )
    {
      preg_match($format, $num, $parts);
      if ( !isset($parts[1]) && !isset($parts[2]) )
        continue;
      $replace = array(
        '%row%'  => $parts[1],
        '%rown%' => $parts[1],
        '%rowm%' => $parts[1],
        '%num%'  => $parts[2]+$hop,
      );
      $next = str_replace(array_keys($replace), array_values($replace), $params['format']);
      
      if ( isset($seats[$next]) )
      {
        // if there is a match, create the link
        $link = new SeatLink;
        $link->seat1 = $seat->id;
        $link->seat2 = $seats[$next]->id;
        $link->save();
        
        if ( sfConfig::get('sf_web_debug') )
          error_log(
            'Creating a link for plan '.$request->getParameter('id').' between seats '.
            $num.' & '.$next.
            ' ('.$link->seat1.' & '.$link->seat2.')'.
            ''
          );
        $cpt++;
      }
    }
    
    $this->result = array('qty' => $cpt);
    
    if (!( sfConfig::get('sf_web_debug', false) && $request->getParameter('debug') ))
      sfConfig::set('sf_web_debug', false);
  }
  public function executeGetLinks(sfWebRequest $request)
  {
    return require(__DIR__.'/get-links.php');
  }
  public function executeLinksRemove(sfWebRequest $request)
  {
    $this->preLinks($request);
    $params = $this->getLinksParameters($request);
    
    if ( !isset($params['exceptions_to_remove']) )
      throw new liSeatedException('The provided informations for this action are not correct.');
    
    $pid = $request->getParameter('id');
    
    foreach ( $this->linksParseSeatsString($params['exceptions_to_remove']) as $seats )
    {
      $fieldname = $seats[2];
      $q = Doctrine::getTable('SeatLink')->createQuery('sl')
        ->   where('sl.seat1 = (SELECT s1.id FROM Seat s1 WHERE s1.'.$fieldname.' = ? AND s1.seated_plan_id = ?) OR sl.seat2 = (SELECT s2.id FROM Seat s2 WHERE s2.'.$fieldname.' = ? AND s2.seated_plan_id = ?)', array($seats[0], $pid, $seats[0], $pid))
        ->andWhere('sl.seat1 = (SELECT s3.id FROM Seat s3 WHERE s3.'.$fieldname.' = ? AND s3.seated_plan_id = ?) OR sl.seat2 = (SELECT s4.id FROM Seat s4 WHERE s4.'.$fieldname.' = ? AND s4.seated_plan_id = ?)', array($seats[1], $pid, $seats[1], $pid))
        ->delete();
      $q->execute();
      
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Seat link deleted: '.$seats[0].' - '.$seats[1]);
    }
    return sfView::NONE;
  }
  public function executeLinksAdd(sfWebRequest $request)
  {
    $this->preLinks($request);
    $params = $this->getLinksParameters($request);
    
    if ( !isset($params['exceptions_to_add']) )
      throw new liSeatedException('The provided informations for this action are not correct.');
    
    $pid = $request->getParameter('id');
    
    foreach ( $this->linksParseSeatsString($params['exceptions_to_add']) as $seats )
    {
      // find back the seats
      $fieldname = $seats[2];
      unset($seats[2]);
      $q = Doctrine::getTable('Seat')->createQuery('s')
        ->andWhereIn("s.$fieldname", $seats)
        ->andWhere('s.seated_plan_id = ?', $pid)
      ;
      $seats = $q->execute();
      
      if ( $seats->count() != 2 )
        throw new liSeatedException('To create a link between seats, two seats are expected, '.$seats->count().' found.');
      
      // creates the link
      $sl = new SeatLink;
      for ( $i = 1 ; $i <= 2 ; $i++ )
        $sl->{'seat'.$i} = $seats[$i-1];
      $sl->save();
      
      // avoid multiple links between the same seats
      $sls = Doctrine::getTable('SeatLink')->createQuery('sl')
        ->   where('sl.seat1 = ? OR sl.seat2 = ?', array($seats[0]->id, $seats[0]->id))
        ->andWhere('sl.seat1 = ? OR sl.seat2 = ?', array($seats[1]->id, $seats[1]->id))
        ->execute();
      while ( $sls->count() > 1 )
        $sls[0]->delete();
    }
    return sfView::NONE;
  }
  protected function linksParseSeatsString($string)
  {
    $r = array();
    foreach ( explode(',', str_replace(', ',',',$string)) as $link )
    {
      $fieldname = 'name';
      if ( substr($link, 0, 8) === 'eve-ids-' )
      {
        $fieldname = 'id';
        $link = substr($link, 8);
      }
      $seats = explode('--', $link, 2);
      $seats[] = $fieldname;
      
      $r[] = $seats;
    }
    
    return $r;
  }
  
  // Seat ranks definition
  public function executeBatchSeatSetRank(sfWebRequest $request)
  {
    return require(__DIR__.'/batch-seat-set-rank.php');
  }
  public function executeSeatSetRank(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array()) ))
      throw new liSeatedException('Given data do not permit the seat recording (no data).');
    if ( !(isset($data['rank']) && intval($data['rank']) > 0) || intval($request->getParameter('id',0)) <= 0 || intval($data['id']) <= 0 )
      throw new liSeatedException('Given data do not permit the seat recording (bad data).');
    
    $seat = Doctrine::getTable('Seat')->findOneById($data['id']);
    if ( !$seat )
      throw new liSeatedException('Given data do not permit the seat recording (bad seat id).');
    
    $seat->rank = $data['rank'];
    $seat->save();
    
    return sfView::NONE;
  }
  
  public function executeSeatAdd(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array()) ))
      throw new liSeatedException('Given data do not permit the seat recording (no data).');
    if ( !$request->hasParameter('id') )
      throw new liSeatedException('Given data do not permit the seat recording (no data).');
    if ( !isset($data['x']) || !isset($data['y']) || !isset($data['diameter']) || !isset($data['name']) || intval($request->getParameter('id',0)) <= 0 )
      throw new liSeatedException('Given data do not permit the seat recording (bad data).');
    
    $seat = new Seat;
    $seat->seated_plan_id = $request->getParameter('id');
    foreach ( array('name', 'x', 'y', 'diameter', 'class',) as $fieldName )
      $seat->$fieldName = $data[$fieldName];
    $seat->save();
    
    $this->json = array();
    if ( $seat->id )
      $this->json['success'] = array('id' => $seat->id);
    else
      $this->json['error'] = true;
  }
  
  public function executeSeatDel(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array()) ))
      throw new liSeatedException('Given data do not permit the seat deletion (no data).');
    if ( !isset($data['id']) || !intval($request->getParameter('id',0)) > 0 )
      throw new liSeatedException('Given data do not permit the seat deletion (bad data).');
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->andWhere('s.seated_plan_id = ?', $request->getParameter('id'))
      ->andWhere('s.id = ?', $data['id'])
      ->andWhere('s.id NOT IN (SELECT tck.seat_id FROM Ticket tck WHERE tck.seat_id IS NOT NULL)');
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->json = array('success' => false, 'message' => __('You cannot remove this seat, probably at least one ticket has been sold on it.'));
    if ( $q->count() > 0 )
    {
      $this->json['success'] = true;
      $this->json['message'] = '';
    }
    $q->delete()->execute();
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
    $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
      ->orderBy('s.name')
    ;
    if ( $request->getParameter('transaction_id',false) )
      $q->leftJoin('sp.Seats s ON sp.id = s.seated_plan_id OR s.id IN (SELECT hc.seat_id FROM Hold h LEFT JOIN h.HoldContents hc LEFT JOIN h.HoldTransactions ht WHERE ht.transaction_id = ?)', $request->getParameter('transaction_id'));
    else
      $q->leftJoin('sp.Seats s');
    
    if ( $request->getParameter('id',false) )
      $q->andWhere('sp.id = ?',$request->getParameter('id'));
    if ( $request->getParameter('gauge_id',false) )
    {
      // if only gauge_id is set
      $q->leftJoin('sp.Workspaces ws')
        ->leftJoin('ws.Gauges g')
        ->leftJoin('g.Manifestation m')
        ->andWhere('sp.location_id = m.location_id')
        ->andWhere('g.id = ?', $request->getParameter('gauge_id',0))
      ;
    }
    $this->seated_plan = $q->fetchOne();
    
    $this->forward404Unless($this->seated_plan);
    $this->form = $this->configuration->getForm($this->seated_plan);
    
    if ( $request->getParameter('gauge_id',false) )
      $this->form->gauge_id = $request->getParameter('gauge_id');
  }
}

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
        ->leftJoin('tck.Seat s')
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Contact c')
        ->leftJoin('t.Professional pro')
        ->leftJoin('pro.Organism org')
        ->leftJoin('pro.Contact pc')
        ->leftJoin('t.Order o')
        ->leftJoin('s.SeatedPlan sp')
        ->leftJoin('tck.Cancelling cancel')
        ->andWhere('tck.cancelling IS NULL')
        ->andWhere('duplicatas.id IS NULL AND cancel.id IS NULL')
        ->andWhere('tck.seat_id IS NOT NULL')
        ->andWhere('sp.id = ?', $request->getParameter('id'))
        ->leftJoin('tck.Manifestation m')
        ->leftJoin('m.Gauge g')
        ->andWhere('g.id = ?', $request->getParameter('gauge_id'))
      ;
      
      foreach ( $q->execute() as $ticket )
        $this->occupied[$ticket->Seat->name] = array(
          'type' => ($ticket->printed_at || $ticket->integrated_at ? 'printed' : ($ticket->Transaction->Order->count() > 0 ? 'ordered' : 'asked')).($ticket->transaction_id === $this->transaction_id ? ' in-progress' : ''),
          'transaction_id' => '#'.$ticket->transaction_id,
          'spectator'      => $ticket->Transaction->professional_id ? $ticket->Transaction->Professional->Contact.' '.$ticket->Transaction->Professional : (string)$ticket->Transaction->Contact,
        );
    }
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
    
    $format = '/'.str_replace(array('%row%', '%num%'), array('([a-zA-Z]+)', '([0-9]+)'), $params['format']).'/';
    $hop = isset($params['contiguous']) ? 1 : 2;
    
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
      $i = intval($parts[2])+$hop;
      
      if ( isset($seats[$parts[1].$i]) )
      {
        // if there is a match, create the link
        $link = new SeatLink;
        $link->seat1 = $seat->id;
        $link->seat2 = $seats[$parts[1].$i]->id;
        $link->save();
        
        if ( sfConfig::get('sf_web_debug') )
          error_log(
            'Creating a link for plan '.$request->getParameter('id').' between seats '.
            $num.' & '.$parts[1].$i.
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
    $this->preLinks($request);
    
    $this->data = array();
    foreach ( $this->getRoute()->getObject()->getLinks() as $link )
    {
      $a = $link[0];
      $b = $link[1];
      
      $ab = sqrt(pow($a->x-$b->x,2) + pow($a->y-$b->y,2));
      $ac = $a->x-$b->x;
      $bc = $a->y-$b->y;
      
      if ( $ac == 0 )
        $angle = $a->y < $b->y ? 90 : -90;
      else
      {
        $preangle = rad2deg(atan($bc/$ac));
        if ( $preangle < 0 )
          $angle = $preangle;
        elseif ( $a->x <= $b->x && $a->y <= $b->y )
          $angle =   0 + $preangle;
        elseif ( $a->x >  $b->x && $a->y < $b->y )
          $angle =  90 + $preangle;
        elseif ( $a->x >  $b->x && $a->y >=  $b->y )
          $angle = 180 + $preangle;
        elseif ( $a->x <= $b->x && $a->y >  $b->y )
          $angle = 270 + $preangle;
      }
      
      if ( false )
      if ( $a->name = 'Q8' && $b->name == 'S15' )
      {
        echo $a->x.' <= '.$b->x.' && '.$a->y.' >  '.$b->y;
        echo "\n";
        echo round($preangle).' deg';
        die();
      }
      
      $this->data[] = array(
        'names' => array($a->name, $b->name),
        'ids'   => array($a->id, $b->id),
        'coordinates' => array(
          array($a->x, $a->y),
          array($b->x, $b->y),
        ),
        'angle' => $angle, // in degrees
        'length' => $ab,
      );
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->getParameter('debug') )
      return $this->renderText(print_r($this->data));
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
      $seats = Doctrine::getTable('Seat')->createQuery('s')
        ->andWhereIn("s.$fieldname", $seats)
        ->andWhere('s.seated_plan_id = ?', $pid)
        ->execute();
      
      if ( $seats->count() != 2 )
        throw new liSeatedException('To create a link between seats, two seats are excepted, '.$seats->count().' found.');
      
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
    foreach ( explode(',', str_replace(' ','',$string)) as $link )
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
    return require(dirname(__FILE__).'/batch-seat-set-rank.php');
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
    foreach ( array('name', 'x', 'y', 'diameter') as $fieldName )
      $seat->$fieldName = $data[$fieldName];
    $seat->save();
    
    return sfView::NONE;
  }
  
  public function executeSeatDel(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array()) ))
      throw new liSeatedException('Given data do not permit the seat deletion (no data).');
    if ( !isset($data['name']) || !intval($request->getParameter('id',0)) > 0 )
      throw new liSeatedException('Given data do not permit the seat deletion (bad data).');
    
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

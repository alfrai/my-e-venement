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
 * Gauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PricesPublicForm extends BaseFormDoctrine
{
  public function getModelName()
  {
    return 'Transaction';
  }
  
  public function configure()
  {
    $this->widgetSchema   ['id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['id'] = new sfValidatorInteger(array('required' => false));
    
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->leftJoin('ws.Users u')
      ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema   ['gauge_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['gauge_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Gauge',
      'query' => $q,
    ));
    
    $this->widgetSchema   ['price_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['price_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Price',
    ));
    
    $this->validatorSchema['seat_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Seat',
      'multiple' => true,
      'query' => Doctrine::getTable('Seat')->createQuery('s')
        ->leftJoin('s.SeatedPlan sp')
        ->leftJoin('sp.Workspaces ws')
        ->leftJoin('ws.Gauges g')
        ->leftJoin('sp.Location l')
        ->leftJoin('l.Manifestations m')
        ->leftJoin('m.Gauges mg')
        ->andWhere('g.id = mg.id')
      ,
      'required' => false,
    ));

    $q = Doctrine_Query::create()->from('Transaction t')
      ->andWhere('t.closed = ?', false)
      ->andWhere('t.sf_guard_user_id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema   ['transaction_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['transaction_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'required' => false,
      'query' => $q,
    ));
    
    // limitting the max quantity, especially for prices linked to member cards
    $this->widgetSchema   ['quantity'] = new sfWidgetFormChoice(array('choices' => array(),));
    $this->validatorSchema['quantity'] = new sfValidatorChoice(array('choices' => array(),));
    $this->setMaxQuantity($vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9);
    
    $this->reviewNameFormat();
  }
  
  public function setMaxQuantity($qty)
  {
    if ( $qty < 0 )
      throw new liEvenementException('You cannot set less than 0 as quantity');
    
    $choices = array();
    for ( $i = 0 ; $i <= $qty ; $i++ ) $choices[] = $i;
    $this->widgetSchema   ['quantity']->setOption('choices',$choices);
    $this->validatorSchema['quantity']->setOption('choices',$choices);
    
    return $this;
  }
  
  public function setQuantity($qty)
  {
    if ( $qty < 0 && $qty > count($this->widgetSchema['quantity']->getOption('choices')) )
      throw new liEvenementException('You cannot select a quantity up to max quantity and less than 0');
    
    $this->setDefault('quantity',$qty);
    return $this;
  }
  
  public function setGaugeId($id)
  {
    $gauge = Doctrine::getTable('Gauge')->createQuery('g', false)
      ->andWhere('g.id = ?', $id);
    
    if ( !$gauge )
      throw new liEvenementException("Invalid gauge's id");
    
    $vel = sfConfig::get('app_tickets_vel');
    $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
    if ( !(isset($vel['no_online_limit_from_manifestations']) && $vel['no_online_limit_from_manifestations'])
      && $gauge->Manifestation->online_limit_per_transaction && $gauge->Manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
      $vel['max_per_manifestation'] = $gauge->Manifestation->online_limit_per_transaction;
    $this->setMaxQuantity($vel['max_per_manifestation']);
    
    $this->setDefault('gauge_id',$id);
    $this->reviewNameFormat();
    
    $this->validatorSchema['seat_id']->getOption('query')->andWhere('g.id = ?', $id);
    
    return $this;
  }
  
  public function setPriceId($id)
  {
    if ( $id < 1 )
      throw new liEvenementException("Invalid price's id");
    
    $this->setDefault('price_id',$id);
    $this->reviewNameFormat();
    return $this;
  }
  
  /** PREREQUSITE: no orphans (optional) **/
  public function save($con = NULL)
  {
    $values = $this->getValues();
    $vel = sfConfig::get('app_tickets_vel', array());
    if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false;
    
    // dispatching seats from wips to real tickets
    $tickets = array();
    $wips = array();
    foreach ( $this->object->Tickets as $key => $ticket )
    if ( $ticket->gauge_id == $values['gauge_id'] )
    {
      if ( $ticket->price_id == $values['price_id'] )
        $tickets[($ticket->seat_id ? $ticket->Seat->rank : 'zzz').' - '.$ticket->id] = $ticket;
      if ( $ticket->price_name && !$ticket->price_id )
        $wips[($ticket->seat_id ? $ticket->Seat->rank : 'zzz').' - '.$ticket->id] = $ticket;
    }
    ksort($tickets);
    krsort($wips);
    
    $count = count($tickets);
    for ( $i = 0 ; $i < $count - $values['quantity'] ; $i++ )
      array_pop($tickets)->delete();
    
    if ( $vel['full_seating_by_customer'] )
    {
      // seating tickets using WIPs
      $count = count($tickets);
      for ( $i = 0 ; $i < $values['quantity'] - $count ; $i++ )
      {
        foreach ( $tickets as $ticket )
        if ( !$ticket->seat_id )
        {
          if (!( $wip = array_pop($wips) ))
            break 2;
          $ticket->seat_id = $wip->seat_id;
          $wip->delete();
          $ticket->save();
          $count--;
        }
      }
    }
    
    // adding tickets
    for ( $i = 0 ; $i < $values['quantity'] - $count ; $i++ )
    {
      $ticket = new Ticket;
      $ticket->price_id = $values['price_id'];
      $ticket->gauge_id = $values['gauge_id'];
      $ticket->Transaction = $this->object;
      $ticket->save();
    }
    
    // AT THIS POINT:
    // - ALL THE EXTRA TICKETS HAVE BEEN DELETED
    // - ALL THE POSSIBLE SELECTED SEATS HAVE BEEN AFFECTED TO REAL TICKETS
    
    if ( $vel['full_seating_by_customer'] )
    {
      // what to seat
      $to_seat = array();
      foreach ( $this->object->Tickets as $ticket )
      if ( !$ticket->seat_id )
        $to_seat[] = $ticket;
      
      $seater = new Seater($values['gauge_id']);
      $seats = $seater->findSeats(count($to_seat));
      $keys = $seats->getKeys();
      foreach ( $tickets as $key => $ticket )
      if ( isset($seats[$keys[$key]]) )
        $ticket->Seat = $seats[$keys[$key]];
      
      // NOW ALL THE TICKETS ARE SEATED WHEN IT WAS POSSIBLE (excepted if we ran out of seat)
    }
    
    if ( sfConfig::get('sf_web_debug', false) )
    foreach ( $this->object->Tickets as $ticket )
    if ( $ticket->isModified() || $ticket->isNew() )
      error_log('In transaction #'.$this->object->id.', Seat '.$ticket->Seat.' for gauge '.$ticket->gauge_id.' and price '.$ticket->price_id);
    
    return $this->object->save($con);
  }
  
  protected function reviewNameFormat()
  {
    if ( $this->getDefault('price_id') && $this->getDefault('gauge_id') )
      $this->widgetSchema->setNameFormat('price['.$this->getDefault('gauge_id').']['.$this->getDefault('price_id').'][%s]');
    else
      $this->widgetSchema->setNameFormat('price[%s]');
    
    return $this;
  }
}

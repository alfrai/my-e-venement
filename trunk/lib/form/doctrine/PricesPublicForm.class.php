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
    
    $choices = array();
    for ( $i = 0 ; $i <= sfConfig::get('app_tickets_max_per_manifestation',9) ; $i++ )
      $choices[] = $i;
    $this->widgetSchema   ['quantity'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
    ));
    $this->validatorSchema['quantity'] = new sfValidatorChoice(array(
      'choices' => $choices,
    ));
    
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
    if ( $id < 1 )
      throw new liEvenementException("Invalid gauge's id");
    
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
  
  public function save($con = NULL)
  {
    $values = $this->getValues();
    $vel = sfConfig::get('app_tickets_vel', array());
    if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false;
    if ( $vel['full_seating_by_customer'] )
      $seater = new Seater($values['gauge_id']);
    
    // cleaning out tickets
    $cpt = array('before' => 0, 'seated' => 0);
    foreach ( $this->object->Tickets as $key => $ticket )
    if ( $ticket->gauge_id == $values['gauge_id'] && $ticket->price_id == $values['price_id'] )
    {
      if ( $vel['full_seating_by_customer'] ) // normal tickets
        $seater->addSeat($ticket->Seat);
      unset($this->object->Tickets[$key]);
    }
    
    // preparing the eventuality of a need to auto-seat some tickets
    $to_seat = array();
    if ( !is_array($values['seat_id']) )
      $values['seat_id'] = $values['seat_id'] ? array($values['seat_id']) : array(0);
    $given_seats = $seater->organizeList(
      Doctrine::getTable('Seat')->createQuery('s')
        ->andWhereIn('s.id', $values['seat_id'])
        ->leftJoin('n.Tickets ntck ON ntck.manifestation_id = tck.manifestation_id')
        ->execute()
    );
    $seats_keys = $given_seats->getPrimaryKeys();
    
    // setting up the tickets
    for ( $i = 0 ; $i < $values['quantity'] ; $i++ )
    {
      $ticket = new Ticket;
      $ticket->price_id   = $values['price_id'];
      $ticket->gauge_id   = $values['gauge_id'];
      if ( $vel['full_seating_by_customer'] )
      {
        if ( isset($seats_keys[$i]) && isset($given_seats[$seats_keys[$i]]) )
          $ticket->seat_id  = $given_seats[$seats_keys[$i]]->id;
        else
          $to_seat[] = $ticket;
      }
      $this->object->Tickets[] = $ticket;
    }
    
    if ( $vel['full_seating_by_customer'] )
    {
      $tickets = array();
      foreach ( $this->object->Tickets as $ticket )
      if ( $ticket->isModified() && $ticket->seat_id )
        $tickets[$ticket->seat_id] = $ticket;
      
      // check for orphans
      foreach ( $orphans = $seater->findOrphansWith($given_seats) as $orphan )
      if ( isset($tickets[$orphan->id]) )
      {
        $to_seat[] = $tickets[$orphan->id];
        $tickets[$orphan->id]->seat_id = NULL;
        unset($given_seats[$orphan->id]);
      }
      
      // tickets to seat
      if ( count($to_seat) > 0 )
      {
        $seats = $seater->findSeatsExcludingOrphans(count($to_seat), $given_seats);
        if ( $seats === false )
          throw new liSeatedException('No available seat can be found.');
        foreach ( $seats as $seat )
        {
          $ticket = array_pop($to_seat);
          if ( $ticket instanceof Ticket )
            $ticket->seat_id = $seat->id;
        }
      }
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

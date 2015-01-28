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
  $ticket = $request->getParameter('ticket');
  $ticket['transaction_id'] = $request->getParameter('id');
  
  $form = new sfForm;
  $validators = $form->getValidatorSchema();
  $validators['id'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Ticket',
    'query' => Doctrine::getTable('Ticket')->createQuery('tck')
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.id = ?', $ticket['transaction_id'])
      ->andWhere('t.closed = ?', false),
    'required' => false,
  ));
  $validators['transaction_id'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Transaction',
  ));
  $validators['gauge_id'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Gauge',
  ));
  $validators['numerotation'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Seat',
    'column' => 'name',
    'query' => $q = Doctrine::getTable('Seat')->createQuery('s')
      ->select('s.*')
      ->leftJoin('s.SeatedPlan sp')
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Gauges g')
      ->andWhere('g.id = ?', $ticket['gauge_id'])
      ->leftJoin('g.Manifestation m')
      ->leftJoin('s.Tickets tck WITH tck.manifestation_id = m.id')
      ->andWhere('(tck.id IS NULL OR tck.id = ?)', $ticket['id'] ? $ticket['id'] : 0)
      
      // Holds
      ->leftJoin('s.HoldContents hc')
      ->leftJoin('hc.Hold h WITH h.manifestation_id = m.id')
      ->andWhere('h.id IS NULL')
  ));
  
  $form->bind($ticket);
  if ( !$form->isValid() ) // security checks
    throw new liSeatedException('The submitted data is not correct to give a seat to this ticket... '.$form->getErrorSchema());
  
  if ( isset($ticket['id']) && $ticket['id'] )
    $this->ticket = Doctrine::getTable('Ticket')->findOneById($ticket['id']);
  else // new ticket (give a seat before the price)
  {
    $this->ticket = new Ticket;
    $this->ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
    $this->ticket->gauge_id = $ticket['gauge_id'];
    $this->ticket->vat = 0;
    $this->ticket->value = 0;
    $this->ticket->transaction_id = $ticket['transaction_id'];
  }
  $this->ticket->numerotation = $ticket['numerotation'];
  $this->ticket->save();
  
  return sfView::NONE;

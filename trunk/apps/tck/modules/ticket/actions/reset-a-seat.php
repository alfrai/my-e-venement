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
  $form = new sfForm;
  $validators = $form->getValidatorSchema();
  $validators['gauge_id'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Gauge',
    'query' => Doctrine::getTable('Gauge')->createQuery('g')
      ->select('g.*')
      ->leftJoin('g.Tickets tck')
      ->andWhere('tck.transaction_id = ?', $request->getParameter('id')),
  ));
  $validators['numerotation'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Seat',
    'column' => 'name',
    'query' => Doctrine::getTable('Seat')->createQuery('s')
      ->select('s.*')
      ->leftJoin('s.SeatedPlan sp')
      ->leftJoin('sp.Workspace ws')
      ->leftJoin('ws.Gauges g')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.closed = ?',false)
      ->andWhere('tck.gauge_id = ?',$ticket['gauge_id'])
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL'),
  ));
  
  $form->bind($ticket);
  if ( !$form->isValid() ) // security checks
    throw new liSeatedException("The submitted data are not correct to reset the ticket's seat. ".$form->getErrorSchema());
  
  $this->ticket = Doctrine_Query::create()->from('Ticket tck')
    ->andWhere('tck.gauge_id = ?',$ticket['gauge_id'])
    ->andWhere('tck.numerotation = ?',$ticket['numerotation'])
    ->fetchOne();
  $this->ticket->numerotation = NULL;
  $this->ticket->save();
  
  return sfView::NONE;

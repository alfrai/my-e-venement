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
  if ( sfConfig::get('sf_web_debug', false) && !$request->hasParameter('debug') )
    sfConfig::set('sf_web_debug', false);
  
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
  $validators['transaction_id'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Transaction',
  ));
  $validators['numerotation'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Seat',
    'column' => 'name',
    'query' => Doctrine::getTable('Seat')->createQuery('s')
      ->select('s.*')
      ->leftJoin('s.Tickets tck')
      ->andWhere('tck.gauge_id = ?', $ticket['gauge_id'])
      ->andWhere('tck.transaction_id = ?', $ticket['transaction_id'])
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL')
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.closed = ?',false)
    ,
  ));
  
  try {
    $form->bind($ticket);
    if ( !$form->isValid() ) // security checks
      throw new liSeatedException("The submitted data are not correct to reset the ticket's seat. ".$form->getErrorSchema());
  }
  catch ( liSeatedException $e )
  {
    error_log($e->getMessage());
    $this->json = array('reset-seat-id' => NULL);
    return 'Success';
  }
  
  $this->ticket = Doctrine_Query::create()->from('Ticket tck')
    ->andWhere('tck.gauge_id = ?',$ticket['gauge_id'])
    ->andWhere('tck.transaction_id = ?', $ticket['transaction_id'])
    ->leftJoin('tck.Seat s')
    ->andWhere('s.name = ?',$ticket['numerotation'])
    ->fetchOne();
  $this->json = array('reset-seat-id' => $this->ticket->seat_id);
  
  // WIPs ?
  $this->ticket->seat_id = NULL;
  if ( $this->ticket->price_id )
    $this->ticket->save();
  else
    $this->ticket->delete();
  
  return 'Success';

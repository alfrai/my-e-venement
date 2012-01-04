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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    $values = $request->getParameter('ticket');
    
    $tid = intval(
        $values['transaction_id']
      ? $values['transaction_id']
      : $request->getParameter('id')
    );
    
    if ( !$tid )
      $this->redirect('ticket/sell');
    
    unset($values['prices']);

    $ticket = new Ticket();
    $ticket->transaction_id = $tid;
    $this->form = new TicketForm($ticket);
    
    if ( $values )
    {
      $this->form->bind($values);
      
      if ( $this->form->isValid() )
      {
        $this->tickets = $this->form->save();
        if ( count($this->tickets) != intval($values['nb']) && intval($values['nb']) >= 0 )
        {
          $this->getUser()->setFlash('error',__("This price doesn't exist for this manifestation !"));
          $this->redirect('ticket/ticket?id='.$ticket->transaction_id);
        }
        $this->form->setWidget('contact_id', new sfWidgetFormInputHidden());
        $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $this->tickets)));
      }
    }
    
    $this->transaction = Doctrine::getTable('Transaction')
      ->findOneById($values['transaction_id'] ? $values['transaction_id'] : $request->getParameter('id'));
    
    // available workspaces
    $workspaces = array();
    foreach ( $this->getUser()->getGuardUser()->Workspaces as $ws )
      $workspaces[] = $ws->id;
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.Price tp')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('g.Workspace ws')
      ->andWhereIn('g.workspace_id',$workspaces)
      ->andWhere('t.id = ?',$this->transaction->id)
      ->andWhere('tck.duplicate IS NULL')
      ->orderBy('e.name, m.happens_at, tck.gauge_id, tck.price_name');
    if ( count($values['manifestation_id']) > 0 )
      $q->andWhereIn('m.id',$values['manifestation_id']);
    $this->manifestations = $q->execute();
    
    // ?? but necessary for ajax requests
    $this->setLayout('empty');

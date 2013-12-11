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
  $this->getContext()->getConfiguration()->loadHelpers(array('I18N','Url'));
  
  if (! $this->url_next = $this->getUser()->getFlash('referer') )
  {
    $this->getUser()->setFlash('error', __('You need to come from somewhere mate...'));
    $this->redirect('ticket/sell?id='.$request->getParameter('id'));
  }

  $q = Doctrine::getTable('Transaction')->createQuery('t')
    ->leftJoin('m.Location l')
    ->leftJoin('l.SeatedPlans sp')
    ->leftJoin('sp.Picture p')
    ->leftJoin('tck.Gauge g')
    ->leftJoin('g.Workspace ws')
    ->leftJoin('m.Event e')
    ->andWhere('t.id = ?',$request->getParameter('id',0))
    ->andWhere('tck.gauge_id = ?',$request->getParameter('gauge_id',0))
    ->andWhere('tck.numerotation IS NULL OR tck.numerotation = ?', '')
    ->orderBy('tck.price_name');
  if ( $request->getParameter('toprint',false) && is_array($request->getParameter('toprint')) )
    $q->andwhereIn('tck.id', $request->getParameter('toprint'));
  $this->transaction = $q->fetchOne();
  
  // error
  if ( ! $this->transaction instanceof Transaction )
  {
    $this->getUser()->setFlash('error','An error occured. Please try again.');
    $this->redirect($request->getReferer());
  }
  
  // the seated plan
  $sample_ticket = $this->transaction->Tickets[0];
  $this->seated_plan = $sample_ticket->Manifestation->Location->getWorkspaceSeatedPlan($this->transaction->Tickets[0]->Gauge->workspace_id);
  $this->gauge = $sample_ticket->Gauge;
  
  // if no plan available, try again the previous screen (RISK OF LOOPHOLES...)
  if ( !$this->seated_plan )
    $this->redirect($request->getReferer());

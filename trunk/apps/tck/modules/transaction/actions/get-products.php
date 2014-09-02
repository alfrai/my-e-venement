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
  /**
   * function executeGetProducts
   * @param sfWebRequest $request, given by the framework (required: id, optional: Array|int product_id || (price_id, gauge_id, printed))
   * @return ''
   * @display a json array containing :
   * json:
   * error:
   *   0: boolean true if errorful, false else
   *   1: string explanation
   * success:
   *   success_fields:
   *     manifestations:
   *       data:
   *         type: manifestations
   *         reset: boolean
   *         content: Array (see below)
   *   error_fields: only if any error happens
   *     manifestations: string explanation
   *
   * the data Array is :
   *   [manifestation_id]: integer
   *     id: integer
   *     name: string
   *     happens_at: string (PGSQL format)
   *     ends_at: string
   *     event_url:  xxx (absolute) link
   *     manifestation_url:  xxx (absolute) link
   *     location: string
   *     location_url: xxx (absolute) link
   *     color: string CSS color of the manifestation
   *     gauge_url: xxx (absolute) data to display the global gauge
   *     gauges:
   *       [gauge_id]:
   *         name: xxx
   *         id: integer
   *         url: xxx (absolute) data to calculate / display the gauge
   *         seated_plan_url: xxx (optional) the absolute path to the plan's picture
   *         seated_plan_seats_url: xxx (optional) the absolute path to the seats definition and allocation
   *         available_prices:
   *           []:
   *             id: integer
   *             name: string, short name
   *             description: string, description
   *             value: string, contextualized price w/ currency (for the current manifestation)
   *         prices:
   *           [price_id]:
   *             id: integer
   *             state: enum(NULL, 'printed', 'integrated', 'cancelling')
   *             qty: integer, the quantity of ticket
   *             pit: float, the price including taxes
   *             vat: float, the current VAT value
   *             tep: float, the price excluding taxes
   *             name: string, the price's name
   *             [ids]:
   *               tickets' id
   *             [numerotation]:
   *               tickets' numerotation
   **/

    $this->getContext()->getConfiguration()->loadHelpers('Slug');
    if ( $request->getParameter('id',false) )
    {
      $q = Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.id = ?', $request->getParameter('id'))
        ->leftJoin('tck.Gauge g')
        ->leftJoin('tck.Price p')
        ->leftJoin('tck.Cancelled tckc')
        ->andWhere('tck.id NOT IN (SELECT tt.duplicating FROM ticket tt WHERE tt.duplicating IS NOT NULL)')
      ;
      
      // retrictive parameters
      if ( $pid = $request->getParameter('price_id', false) )
        $q->andWhere('tck.price_id = ? OR tck.price_id IS NULL',$pid);
      if ( $request->hasParameter('state') )
      {
        switch ( $request->getParameter('state') ){
        case 'printed':
          $q->andWhere('tck.printed_at IS NOT NULL');
          break;
        case 'integrated':
          $q->andWhere('tck.integrated_at IS NOT NULL');
          break;
        case 'cancelling':
          $q->andWhere('tck.cancelling IS NOT NULL');
          break;
        default:
          $q->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL');
          break;
        }
      }
    }
    elseif ( $request->getParameter('manifestation_id',false) )
      $q = Doctrine::getTable('Manifestation')->createQuery('m');
    
    $q->leftJoin('m.IsNecessaryTo n')
      ->leftJoin('n.Gauges ng')
    ;
    
    $mid = array();
    if ( $request->getParameter('manifestation_id',false) )
      $mid = is_array($request->getParameter('manifestation_id'))
        ? $request->getParameter('manifestation_id')
        : array($request->getParameter('manifestation_id'));
    
    // retrictive parameters
    if ( $request->getParameter('manifestation_id',false) )
    {
      $q->andWhere('(TRUE')
        ->andWhereIn('n.id',$mid)
        ->orWhereIn('m.id',$mid)
        ->andWhere('TRUE)');
    }
    if ( $gid = $request->getParameter('gauge_id', false) )
      $q->andWhere('(g.id = ? OR ng.id = ? AND g.workspace_id = ng.workspace_id)',array($gid, $gid));
    
    $this->json = array();
    $this->transaction = false;
    if ( $request->getParameter('id',false) )
      $this->transaction = $q->fetchOne();
    elseif ( $q->count() == 0 )
      return;
    
    // model for ticket's data
    $tickets_model = array(
      'state' => '',
      'qty' => 0,
      'pit' => 0,
      'vat' => 0,
      'tep' => 0,
      'extra-taxes' => 0,
      'name' => '',
      'description' => '',
      'id' => '',
      'ids' => array(),
      'numerotation' => array(),
    );
    
    foreach ( $this->transaction ? $this->transaction->Tickets : $mid as $ticket ) // loophole
    {
      // by manifestation
      if ( !isset($this->json[$mid = $ticket instanceof Ticket ? $ticket->manifestation_id : $ticket]) )
      {
        $manifestation = Doctrine::getTable('Manifestation')->createQuery('m',true)
          ->leftJoin('m.PriceManifestations pm')
          ->leftJoin('pm.Price p')
          ->leftJoin('m.Gauges g')
          ->leftJoin('g.Workspace w')
          ->leftJoin('w.Order wuo ON wuo.workspace_id = w.id AND wuo.sf_guard_user_id = ?',$this->getUser()->getId())
          ->orderBy('et.name, me.name, m.happens_at, m.duration, wuo.rank, w.name, p.name')
          ->leftJoin('p.WorkspacePrices pwp ON pwp.price_id = p.id AND pwp.workspace_id = w.id')
          ->leftJoin('p.UserPrices      pup ON pup.price_id = p.id AND pup.sf_guard_user_id = ?',$this->getUser()->getId())
          //->leftJoin('w.WorkspaceUsers wsu ON wsu.workspace_id = w.id AND wsu.sf_guard_user_id = ?',$this->getUser()->getId())
          ->andWhere('m.id = ?',$mid)
          ->fetchOne();
        
        $this->json[$manifestation->id] = array(
          'id'            => $manifestation->id,
          'name'          => (string)$manifestation->Event,
          'event_url'     => cross_app_url_for('event', 'event/show?id='.$manifestation->event_id, true),
          'happens_at'    => (string)$manifestation->happens_at,
          'ends_at'       => (string)$manifestation->ends_at,
          'manifestation_url' => cross_app_url_for('event', 'manifestation/show?id='.$manifestation->id,true),
          'location'      => (string)$manifestation->Location,
          'location_url'  => cross_app_url_for('event', 'location/show?id='.$manifestation->location_id,true),
          'color'         => (string)$manifestation->Color,
          'gauge_url'     => cross_app_url_for('event','',true),
        );
        
        // gauges
        $this->json[$manifestation->id]['gauges'] = array();
        foreach ( $manifestation->Gauges as $gauge )
        {
          $this->json[$manifestation->id]['gauges'][$gauge->id] = array(
            'id' => $gauge->id,
            'name' => (string)$gauge->Workspace,
            'url' => cross_app_url_for('event','gauge/state?id='.$gauge->id.'&json=true',true),
            'available_prices' => array(),
            'prices' => array('-' => $tickets_model),
          );
          
          if ( $seated_plan = $manifestation->Location->getWorkspaceSeatedPlan($gauge->workspace_id) )
          {
            $this->json[$manifestation->id]['gauges'][$gauge->id]['seated_plan_url'] =
              cross_app_url_for('default', 'picture/display?id='.$seated_plan->picture_id);
            $this->json[$manifestation->id]['gauges'][$gauge->id]['seated_plan_seats_url'] =
              cross_app_url_for('event', 'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id.'&transaction_id='.$this->transaction->id);
          }
          
          // seated plans
          if ( $seated_plan = $manifestation->Location->getWorkspaceSeatedPlan($gauge->workspace_id) )
          {
            $this->json[$manifestation->id]['gauges'][$gauge->id]['seated_plan_url']
              = cross_app_url_for('default', 'picture/display?id='.$seated_plan->picture_id,true);
            $this->json[$manifestation->id]['gauges'][$gauge->id]['seated_plan_seats_url']
              = cross_app_url_for('event',   'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id.($this->transaction ? '&transaction_id='.$this->transaction->id : ''),true);
          }
        
          // available prices
          foreach ( $manifestation->PriceManifestations as $pm )
          {
            // this price is correctly associated to this gauge
            $pw = false;
            foreach ( $pm->Price->WorkspacePrices as $pwp )
            if ( $pwp->workspace_id === $gauge->workspace_id )
            {
              $pw = true;
              break;
            }
            if ( !$pw ) continue;
            
            // access to this meta event
            if ( !in_array($manifestation->Event->meta_event_id, array_keys($this->getUser()->getMetaEventsCredentials())) )
              continue;
            
            // access to this price
            if ( $pm->Price->UserPrices->count() == 0 )
              continue;
            
            // access to this workspace
            if ( !in_array($gauge->workspace_id, array_keys($this->getUser()->getWorkspacesCredentials())) )
              continue;
            
            // then add the price...
            $this->json[$manifestation->id]['gauges'][$gauge->id]['available_prices'][] = array(
              'id'  => $pm->price_id,
              'name'  => $pm->Price->name,
              'description'  => $pm->Price->description,
              'value' => format_currency($pm->value,'€'),
            );
          }
        }
      }
      
      if (! $ticket instanceof Ticket )
        continue;
      
      // by price
      $state = NULL;
      if ( $ticket->cancelling )
        $state = 'cancelling';
      elseif ( $ticket->printed_at )
        $state = 'printed';
      elseif ( $ticket->integrated_at )
        $state = 'integrated';
      
      $pname = $ticket->price_id.'-'.$state;
      if (!( isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname])
          && count($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['ids']) > 0
      ))
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname] = array(
          'state' => $state,
          'name' => !$ticket->price_id ? $ticket->price_name : $ticket->Price->name,
          'description' => !$ticket->price_id ? '' : $ticket->Price->description,
          'id' => $ticket->price_id ? $ticket->price_id : slugify($ticket->price_name),
        ) + $tickets_model;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['ids'][] = $ticket->id;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['numerotation'][] = $ticket->numerotation;
      
      // by group of tickets
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['qty']++;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['extra-taxes'] += $ticket->taxes;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['pit'] += $ticket->value;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['tep'] += $tep = round(($ticket->value+$ticket->taxes)/(1+$ticket->vat),2);
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['vat'] += $ticket->value + $ticket->taxes - $tep;
      
      // cancelling tickets
      if ( $cancelling = $ticket->hasBeenCancelled() )
      {
        $state = 'cancelling';
        $pname = $ticket->price_id.'-'.$state;
        if (!( isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]) && count($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['ids']) == 0 ))
          $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname] = 
          $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname] = array(
            'state' => $state,
            'name' => !$ticket->price_id ? $ticket->price_name : $ticket->Price->name,
            'description' => !$ticket->price_id ? '' : $ticket->Price->description,
            'id' => $ticket->price_id ? $ticket->price_id : slugify($ticket->price_name),
          ) + $tickets_model;
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['ids'][] = $cancelling[0]->id;
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['numerotation'][] = $cancelling[0]->numerotation;
        
        // by group of tickets
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['qty']--;
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['extra-taxes'] += $cancelling[0]->taxes;
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['pit'] += $cancelling[0]->value;
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['tep'] += $tep = round(($cancelling[0]->value+$cancelling[0]->taxes)/(1+$cancelling[0]->vat),2);
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['vat'] += $cancelling[0]->value + $cancelling[0]->taxes - $tep;
      }
    }
    
    foreach ( $this->json as $mid => $manif )
    foreach ( $manif['gauges'] as $gid => $gauge )
    if ( count($gauge['prices']) == 0 && count($gauge['available_prices']) == 0 )
      unset($this->json[$mid]['gauges'][$gid]);
    
    $this->json = array(
      'error' => array(false, ''),
      'success' => array(
        'success_fields' => array(
          'manifestations' => array(
            'data' => array(
              'type' => 'manifestations',
              'reset' => $this->transaction ? true : false,
              'content' => $this->json,
            ),
          ),
        ),
        'error_fields' => array(),
      ),
    );

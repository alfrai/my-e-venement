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
   * function executeGetManifestations
   * @param sfWebRequest $request, given by the framework (required: id, optional: manifestation_id || (price_id, gauge_id, printed))
   * @return ''
   * @display a json array containing :
   * json:
   *   [manifestation_id]: integer
   *     id: integer
   *     name: string
   *     happens_at: string (PGSQL format)
   *     ends_at: string
   *     event_url:  xxx (absolute) link
   *     manifestation_url:  xxx (absolute) link
   *     location: string
   *     location_url: xxx (absolute) link
   *     gauge_url: xxx (absolute) data to display the global gauge
   *     gauges:
   *       [gauge_id]:
   *         name: xxx
   *         id: integer
   *         url: xxx (absolute) data to display the gauge
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
   *             printed: boolean
   *             cancelling: boolean
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

    $q = Doctrine::getTable('Transaction')->createQuery('t')
      ->andWhere('t.id = ?', $request->getParameter('id'))
      ->leftJoin('tck.Gauge g')
      ->andWhere('tck.duplicating IS NULL'); // TODO: to be performed
    
    // retrictive parameters
    if ( $mid = $request->getParameter('manifestation_id', false) )
      $q->andWhere('m.id = ?',$mid);
    if ( $gid = $request->getParameter('gauge_id', false) )
      $q->andWhere('g.id = ?',$gid);
    if ( $pid = $request->getParameter('price_id', false) )
      $q->andWhere('tck.price_id = ?',$pid);
    if ( $request->hasParameter('printed') )
    {
      if ( in_array($request->getParameter('printed'),array('0','false')) )
        $q->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL');
      else
        $q->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL');
    }
    
    $this->transaction = $q->fetchOne();
    $this->json = array();
    if ( !$this->transaction )
      return;
    
    foreach ( $this->transaction->Tickets as $ticket )
    {
      // by manifestation
      if ( !isset($this->json[$ticket->manifestation_id]) )
      {
        $manifestation = Doctrine::getTable('Manifestation')->createQuery('m',true)
          ->leftJoin('m.PriceManifestations pm')
          ->leftJoin('pm.Price p')
          ->leftJoin('m.Gauges g')
          ->leftJoin('g.Workspace w')
          ->leftJoin('w.Order wuo ON wuo.workspace_id = w.id AND wuo.sf_guard_user_id = '.$this->getUser()->getId())
          ->orderBy('e.name, me.name, m.happens_at, m.duration, wuo.rank, w.name, p.name')
          ->leftJoin('p.WorkspacePrices pwp ON pwp.price_id = p.id AND pwp.workspace_id = w.id')
          ->leftJoin('p.UserPrices      pup ON pup.price_id = p.id AND pup.sf_guard_user_id = '.$this->getUser()->getId())
          ->andWhere('m.id = ?',$ticket->manifestation_id)
          ->fetchOne();
        
        $this->json[$manifestation->id] = array(
          'id'   => $manifestation->id,
          'name' => (string)$manifestation->Event,
          'event_url' => cross_app_url_for('event', 'event/show?id='.$manifestation->event_id, true),
          'happens_at' => (string)$manifestation->happens_at,
          'ends_at' => (string)$manifestation->ends_at,
          'manifestation_url'  => cross_app_url_for('event', 'manifestation/show?id='.$manifestation->id,true),
          'location' => (string)$manifestation->Location,
          'location_url' => cross_app_url_for('event', 'location/show?id='.$manifestation->location_id,true),
          'gauge_url' => cross_app_url_for('event','',true),
        );
        
        // gauges
        $this->json[$manifestation->id]['gauges'] = array();
        foreach ( $manifestation->Gauges as $gauge )
        {
          $this->json[$manifestation->id]['gauges'][$gauge->id] = array(
            'id' => $gauge->id,
            'name' => (string)$gauge->Workspace,
            'url' => cross_app_url_for('event','',true)
          );
          
          
          // seated plans
          if ( $seated_plan = $manifestation->Location->getWorkspaceSeatedPlan($gauge->workspace_id) )
          {
            $this->json[$manifestation->id]['gauges'][$gauge->id]['seated_plan_url']
              = cross_app_url_for('default', 'picture/display?id='.$seated_plan->picture_id,true);
            $this->json[$manifestation->id]['gauges'][$gauge->id]['seated_plan_seats_url']
              = cross_app_url_for('event',   'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id.'&transaction_id='.$this->transaction->id,true);
          }
        
          // available prices
          $this->json[$manifestation->id]['available_prices'] = array();
          foreach ( $manifestation->PriceManifestations as $pm )
          {
            $pw = false;
            foreach ( $pm->Price->WorkspacePrices as $pwp )
            if ( $pwp->workspace_id === $gauge->workspace_id )
            {
              $pw = true;
              break;
            }
            
            if ( $pm->Price->UserPrices->count() > 0 && $pw )
              $this->json[$manifestation->id]['gauges'][$gauge->id]['available_prices'][] = array(
                'id' => $pm->Price->id,
                'name' => $pm->Price->name,
                'description' => $pm->Price->description,
                'value' => format_currency($pm->value,'€'),
              );
          }
        }
      }
      
      // by price
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices']) )
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'] = array();
      $pname = $ticket->price_id.'-'
        .($ticket->cancelling ? 'cancel' : 'normal').'-'
        .($ticket->printed_at || $ticket->integrated_at ? 'done' : 'todo')
      ;
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]) )
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname] = array(
          'printed' => $ticket->printed_at || $ticket->integrated_at,
          'cancelling' => $ticket->cancelling ? true : false,
          'qty' => 0,
          'pit' => 0,
          'vat' => 0,
          'tep' => 0,
          'name' => '',
          'description' => '',
          'id' => $ticket->price_id,
          'ids' => array(),
          'numerotation' => array()
        );
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['ids'][] = $ticket->id;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['numerotation'][] = $ticket->numerotation;
      
      // by group of tickets
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['name'] = $ticket->Price->name;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['description'] = $ticket->Price->description;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['qty']++;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['pit'] += $ticket->value;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['tep'] += $tep = round($ticket->value/(1+$ticket->vat),2);
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['vat'] += $ticket->value - $tep;
    }

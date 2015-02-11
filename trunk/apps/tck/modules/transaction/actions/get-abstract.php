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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  /**
   * function executeGetManifestations
   * @param sfWebRequest $request, given by the framework (required: id, optional: Array|int manifestation_id || (price_id, gauge_id, printed))
   * @return ''
   * @display a json array containing :
   * json:
   * error:
   *   0: boolean true if errorful, false else
   *   1: string explanation
   * success:
   *   success_fields:
   *     manifestations|store:
   *       data:
   *         type: manifestations|store
   *         reset: boolean
   *         content: Array (see below)
   *   error_fields: only if any error happens
   *     manifestations: string explanation
   *
   * the data Array is :
   *   [manifestation_id|product_id]: integer
   *     id: integer
   *     name: string
   *     (happens_at: string (PGSQL format))
   *     (ends_at: string)
   *     category_url:  xxx (absolute) link
   *     product_url:  xxx (absolute) link
   *     (location: string)
   *     (location_url: xxx (absolute) link)
   *     description: string, description
   *     color: string CSS color of the manifestation
   *     (declination_url: xxx (absolute) data to display the global gauge)
   *     declinations_name: string, "gauges"
   *     gauges:
   *       [gauge_id]:
   *         name: xxx
   *         id: integer
   *         type: string, 'gauge'|'pdt-declination'
   *         url: NULL|string, xxx (absolute) data to calculate / display the gauge
   *         (seated_plan_url: string, xxx (optional) the absolute path to the plan's picture
   *         (seated_plan_width: integer, (optional) the ideal width of the plan's picture, if one is set
   *         (seated_plan_seats_url: string, xxx (optional) the absolute path to the seats definition and allocation)
   *         description: string, description
   *         available_prices:
   *           []:
   *             id: integer
   *             name: string, short name
   *             description: string, description
   *             value: string, contextualized price w/ currency (for the current manifestation)
   *             raw_value: float, contextualized price w/o currency
   *             currency: string, currency
   *         prices:
   *           [price_id]:
   *             id: integer
   *             state: enum(NULL, 'printed', 'integrated', 'cancelling') for manifs | enum(NULL, 'integrated') for products
   *             qty: integer, the quantity of ticket
   *             pit: float, the price including taxes
   *             vat: float, the current VAT value
   *             tep: float, the price excluding taxes
   *             name: string, the price's name
   *             description: string, the price's description
   *             item-details: boolean
   *             [ids]:
   *               tickets' or products' ids
   *             ([ids_url]:)
   *               details of tickets url
   *             ([numerotation]:)
   *               tickets' numerotation
   **/

    $this->getContext()->getConfiguration()->loadHelpers('Slug');
    
    $fct = 'createQueryFor'.ucfirst($type);
    if ( $request->getParameter('id',false) )
    {
      $table = Doctrine::getTable('Transaction');
      if ( !method_exists($table, $fct) )
        $fct = 'createQuery';
      $q = Doctrine::getTable('Transaction')->$fct('t');
      if ( $type == 'store' )
        $q->andWhere('t.id = ? OR t.transaction_id = ? AND bp.integrated_at IS NOT NULL', array($request->getParameter('id'), $request->getParameter('id')));
      else
        $q->andWhere('t.id = ?', $request->getParameter('id'));
    }
    
    switch ( $type ){
    case 'manifestations':
      $subobj = 'Ticket';
      $product_id = 'manifestation_id';
      
      if ( $request->getParameter('id',false) )
      {
        $q->leftJoin('m.Event e')
          ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
          ->leftJoin('tck.Gauge g WITH g.onsite = TRUE')
          ->leftJoin('tck.Price p')
          ->leftJoin('tck.Cancelled tckc')
          ->andWhere('tck.id NOT IN (SELECT tt.duplicating FROM ticket tt WHERE tt.duplicating IS NOT NULL)')
        ;
        // retrictive parameters
        if ( $price_id = $request->getParameter('price_id', false) )
          $q->andWhere('tck.price_id = ? OR tck.price_id IS NULL', $price_id);
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
      else
        $q = Doctrine::getTable('Manifestation')->createQuery('m');
      
      $q->leftJoin('m.IsNecessaryTo n')
        ->leftJoin('n.Gauges ng WITH ng.onsite = TRUE')
      ;
      
      // retrictive parameters
      $pid = array();
      if ( $request->getParameter('manifestation_id',false) )
      {
        $pid = is_array($request->getParameter('manifestation_id'))
          ? $request->getParameter('manifestation_id')
          : array($request->getParameter('manifestation_id'));
        $q->andWhere('(TRUE')
          ->andWhereIn('n.id',$pid)
          ->orWhereIn('m.id',$pid)
          ->andWhere('TRUE)')
        ;
      }
      if ( $gid = $request->getParameter('gauge_id', false) )
        $q->andWhere('(g.id = ? OR ng.id = ? AND g.workspace_id = ng.workspace_id)',array($gid, $gid));
    
    break;
    case 'store':
      $subobj = 'BoughtProduct';
      $product_id = 'Declination->product_id';
      
      if ( $request->getParameter('id',false) )
      {
        $q->andWhereIn('pdt.meta_event_id IS NULL OR pdt.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()));
        
        // retrictive parameters
        if ( $price_id = $request->getParameter('price_id', false) )
          $q->andWhere('bp.price_id = ? OR bp.price_id IS NULL',$price_id);
        if ( $request->hasParameter('state') )
        {
          switch ( $request->getParameter('state') ){
          case 'related':
          case 'integrated':
            $q->andWhere('bp.integrated_at IS NOT NULL');
            break;
          default:
            $q->andWhere('bp.integrated_at IS NULL');
            break;
          }
        }
      }
      else
        $q = Doctrine::getTable('Product')->createQuery('pdt');
      
      // retrictive parameters
      $pid = array();
      if ( $request->getParameter('product_id',false) )
      {
        $pid = is_array($request->getParameter('product_id'))
          ? $request->getParameter('product_id')
          : array($request->getParameter('product_id'));
        $q->andWhereIn('pdt.id',$pid);
      }
      if ( $did = $request->getParameter('declination_id', false) )
        $q->andWhere('d.id = ?', $did);
      
      break;
    }
    
    $this->json = array();
    $this->transaction = false;
    if ( $request->getParameter('id',false) )
      $this->transaction = $q->fetchOne();
    elseif ( $q->count() == 0 )
      return;
    
    // model for ticket's data
    $items_model = array(
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
    
    foreach ( $this->transaction ? $this->transaction[$subobj.'s'] : $pid as $item ) // loophole
    {
      // by manifestation/product
      $obj = $item;
      foreach ( explode('->', $product_id) as $field )
      if ( is_object($obj) )
        $obj = $obj->$field;
      $id = intval($obj);
      
      if ( !isset($this->json[$id]) )
      {
        switch ( $type ) {
        case 'manifestations':
          $subobj = 'Gauge';
          $q = Doctrine::getTable('Manifestation')->createQuery('m',true)
            ->leftJoin('m.PriceManifestations pm')
            ->leftJoin('pm.Price pmp WITH pmp.hide = ?', false)
            ->leftJoin('pmp.Translation pmpt WITH pmpt.lang = ?', $this->getUser()->getCulture())
            ->leftJoin('m.Gauges g WITH g.onsite = TRUE')
            ->leftJoin('g.PriceGauges pg')
            ->leftJoin('pg.Price pgp WITH pgp.hide = ?', false)
            ->leftJoin('pgp.Translation pgpt WITH pgpt.lang = ?', $this->getUser()->getCulture())
            ->leftJoin('g.Workspace w')
            ->leftJoin('w.Order wuo ON wuo.workspace_id = w.id AND wuo.sf_guard_user_id = ?',$this->getUser()->getId())
            ->orderBy('et.name, me.name, m.happens_at, m.duration, wuo.rank, w.name, pmpt.name, pgpt.name')
            ->leftJoin('pmp.WorkspacePrices pmpwp WITH pmpwp.workspace_id = w.id')
            ->leftJoin('pmp.UserPrices      pmpup WITH pmpup.sf_guard_user_id = ?',$this->getUser()->getId())
            ->leftJoin('pgp.WorkspacePrices pgpwp WITH pgpwp.workspace_id = w.id')
            ->leftJoin('pgp.UserPrices      pgpup WITH pgpup.sf_guard_user_id = ?',$this->getUser()->getId())
            //->leftJoin('w.WorkspaceUsers wsu ON wsu.workspace_id = w.id AND wsu.sf_guard_user_id = ?',$this->getUser()->getId())
            ->andWhere('m.id = ?',$id)
          ;
          if ( $gid = $request->getParameter('gauge_id', false) )
            $q->andWhere('g.id = ?', $gid);
          $product = $q->fetchOne();
          
          $this->json[$product->id] = array(
            'id'            => $product->id,
            'name'          => NULL,
            'category'      => (string)$product->Event,
            'description'   => $product->Event->description,
            'happens_at'    => (string)$product->happens_at,
            'ends_at'       => (string)$product->ends_at,
            'category_url'  => cross_app_url_for('event', 'event/show?id='.$product->event_id, true),
            'product_url'   => cross_app_url_for('event', 'manifestation/show?id='.$product->id,true),
            'location'      => (string)$product->Location,
            'location_url'  => cross_app_url_for('event', 'location/show?id='.$product->location_id,true),
            'color'         => (string)$product->Color,
            'declination_url'   => cross_app_url_for('event','',true),
            'declinations_name' => 'gauges',
          );
          break;
        case 'store':
          $subobj = 'Declination';
          $q = Doctrine::getTable('Product')->createQuery('p')
            ->leftJoin('p.Category c')
            ->leftJoin('c.Translation ct WITH ct.lang = ?', $this->getUser()->getCulture())
            ->leftJoin('p.PriceProducts pp')
            ->leftJoin('pp.Price price WITH price.hide = ? AND price.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = ?)', array(false, $this->getUser()->getId()))
            ->leftJoin('price.Translation prt WITH prt.lang = ?', $this->getUser()->getCulture())
            ->orderBy('pt.name, dt.name, prt.name')
            ->leftJoin('price.UserPrices pup WITH pup.sf_guard_user_id = ?',$this->getUser()->getId())
            ->leftJoin('p.MetaEvent pme')
            ->andWhereIn('pme.id IS NULL OR pme.id', array_keys($this->getUser()->getMetaEventsCredentials()))
            ->andWhere('p.id = ?',$id)
          ;
          if ( $request->getParameter('product_id',false) )
          {
            $pid = is_array($request->getParameter('product_id'))
              ? $request->getParameter('product_id')
              : array($request->getParameter('product_id'));
            $q->andWhereIn('p.id',$pid);
          }
          if ( $did = $request->getParameter('declination_id', false) )
            $q->andWhere('d.id = ?', $did);
          
          if (!( $product = $q->fetchOne() ) && $id == 0 )
          {
            $product = new Product;
            $product->name = $item->name;
            $product->id = slugify($item->name);
            
            $declination = new ProductDeclination;
            $declination->name = $item->declination;
            $declination->id = slugify($item->declination);
            $product->Declinations[] = $declination;
          }
          
          $this->json[$product->id] = array(
            'id'            => $product->id,
            'name'          => (string)$product,
            'category'      => (string)$product->Category,
            'description'   => $product->description,
            'category_url'  => cross_app_url_for('pos', 'category/show?id='.$product->product_category_id,true),
            'product_url'   => cross_app_url_for('pos', 'product/show?id='.$product->id, true),
            'color'         => NULL,
            'declinations_url'  => NULL,
            'declinations_name' => 'declinations',
          );
          break;
        }
        
        // gauges
        $this->json[$product->id][$this->json[$product->id]['declinations_name']] = array();
        $cpt = 0;
        foreach ( $product[$subobj.'s'] as $declination )
        {
          $this->json[$product->id][$this->json[$product->id]['declinations_name']][$declination->id] = array(
            'id' => $declination->id,
            'sort' => $cpt,
            'name' => (string)$declination,
            'url' => cross_app_url_for('event','gauge/state?id='.$declination->id.'&json=true',true),
            'type' => strtolower($subobj),
            'description' => NULL,
            'available_prices' => array(),
            'prices' => array('-' => $items_model),
          );
          $cpt++;
          
          switch ( $subobj ) {
          case 'Gauge':
            // seated plans
            if ( $seated_plan = $product->Location->getWorkspaceSeatedPlan($declination->workspace_id) )
            {
              $this->json[$product->id][$this->json[$product->id]['declinations_name']][$declination->id]['seated_plan_url']
                = cross_app_url_for('default', 'picture/display?id='.$seated_plan->picture_id,true);
              $this->json[$product->id][$this->json[$product->id]['declinations_name']][$declination->id]['seated_plan_seats_url']
                = cross_app_url_for('event',   'seated_plan/getSeats?ticketting=true&id='.$seated_plan->id.'&gauge_id='.$declination->id.($this->transaction ? '&transaction_id='.$this->transaction->id : ''),true);
              if ( $seated_plan->ideal_width )
              $this->json[$product->id][$this->json[$product->id]['declinations_name']][$declination->id]['seated_plan_width']
                = $seated_plan->ideal_width;
            }
            break;
          }
          
          // available prices
          $prices = array();
          switch ( $type ) {
          case 'manifestations':
            $pw = false;
            $pps = array();
            // priority to PriceGauge as it is in the model + ordering
            foreach ( array($declination->PriceGauges, $product->PriceManifestations) as $data )
            foreach ( $data as $pp )
            if ( !isset($pps[$pp->price_id]) )
              $pps[$pp->price_id] = $pp;
            foreach ( $pps as $i => $pp )
            if ( $pp->Price instanceof Price )
            {
              unset($pps[$i]);
              $pps[str_pad($pp->value,10,'0',STR_PAD_LEFT).'|'.$pp->Price->name.'|'.$i] = $pp;
            }
            krsort($pps);
            
            foreach ( $pps as $pp )
            {
              // this price is correctly associated to this gauge
              if ( $pp->Price && !in_array($declination->workspace_id, $pp->Price->Workspaces->getPrimaryKeys()) )
                continue;
              // access to this workspace
              if ( !in_array($declination->workspace_id, array_keys($this->getUser()->getWorkspacesCredentials())) )
                continue;
              // access to this meta event
              if ( !in_array($product->Event->meta_event_id, array_keys($this->getUser()->getMetaEventsCredentials())) )
                continue;
              
              $prices[] = $pp;
            }
            break;
          case 'store':
            foreach ( $product->PriceProducts as $pp )
            {
              // access to this meta event
              if ( !is_null($product->meta_event_id) && !in_array($product->meta_event_id, array_keys($this->getUser()->getMetaEventsCredentials())) )
                continue;
              
              $prices[] = $pp;
            }
            break;
          }
          
          // process available prices
          foreach ( $prices as $pp )
          {
            // access to this price
            if (!( $pp->Price && $pp->Price->UserPrices->count() > 0 ))
              continue;
            
            // then add the price...
            $this->json[$product->id][$this->json[$product->id]['declinations_name']][$declination->id]['available_prices'][] = array(
              'id'  => $pp->price_id,
              'name'  => (string)$pp->Price,
              'description'  => $pp->Price->description,
              'value' => format_currency($pp->value,'€'),
              'raw_value' => floatval($pp->value),
              'currency' => '€',
            );
          }
        }
      }
      
      if (! $item instanceof Itemable )
        continue;
      
      // by price
      $state = $declination = NULL;
      switch ( $this->json[$product->id]['declinations_name'] ) {
      case 'gauges':
        $declination = $item->Gauge;
        $pid = $item->Gauge->manifestation_id;
        if ( $item->cancelling )
          $state = 'cancelling';
        elseif ( $item->printed_at )
          $state = 'printed';
        elseif ( $item->integrated_at )
          $state = 'integrated';
        break;
      case 'declinations':
        if ( $item->product_declination_id )
        {
          $declination = $item->Declination;
          $pid = $item->Declination->product_id;
        }
        else
        {
          // this is to print out BoughtProducts that has no link to a real product
          $pid = slugify($item->name);
          $declination = new ProductDeclination;
          $declination->id = slugify($item->declination);
          $declination->name = $item->declination;
        }
        if ( $item->transaction_id != $request->getParameter('id') )
          $state = 'cancelling';
        elseif ( !$state && $item->integrated_at )
          $state = 'integrated';
        break;
      }
      
      $pname = ($item->price_id ? $item->price_id : slugify($item->price_name)).'-'.$state;
      if (!( isset($this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname])
          && count($this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['ids']) > 0
      ))
        $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname] = array(
          'state' => $state,
          'name' => !$item->price_id ? $item->price_name : $item->Price->name,
          'description' => !$item->price_id ? '' : $item->Price->description,
          'item-details' => in_array($this->json[$product->id]['declinations_name'], array('gauges')), // the link to a specific place to detail the items
          'id' => $item->price_id ? $item->price_id : slugify($item->price_name),
        ) + $items_model;
      $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['ids'][] = $item->id;
      if ( in_array($this->json[$product->id]['declinations_name'], array('gauges')) )
      {
        $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['ids_url'][] = cross_app_url_for('tck', 'ticket/show?id='.$item->id, true);
        $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['numerotation'][] = $item->numerotation;
      }
      
      // by group of tickets
      $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['qty']++;
      if ( in_array($this->json[$product->id]['declinations_name'], array('gauges')) )
        $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['extra-taxes'] += $item->taxes;
      $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['pit'] += $item->value;
      $real_value = in_array($this->json[$product->id]['declinations_name'], array('gauges'))
        ? $item->value+$item->taxes
        : $item->value;
      $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['tep'] += $tep = round($real_value/(1+$item->vat),2);
      $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['vat'] += $real_value - $tep;
      
      // POST PROD SPECIFICITIES
      switch ( $this->json[$product->id]['declinations_name'] ) {
      case 'gauges':
        // cancelling tickets
        if ( $cancelling = $item->hasBeenCancelled() )
        {
          $state = 'cancelling';
          $pname = $item->price_id.'-'.$state;
          if (!( isset($this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname])
            && count($this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['ids']) > 0
          ))
          {
            $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname] = array(
              'state' => $state,
              'name' => !$item->price_id ? $item->price_name : $item->Price->name,
              'description' => !$item->price_id ? '' : $item->Price->description,
              'item-details' => false,
              'id' => $item->price_id ? $item->price_id : slugify($item->price_name),
            ) + $items_model;
          }
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['ids'][] = $cancelling[0]->id;
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['numerotation'][] = $cancelling[0]->numerotation;
          
          // by group of tickets
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['qty']--;
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['extra-taxes'] += $cancelling[0]->taxes;
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['pit'] += $cancelling[0]->value;
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['tep'] += $tep = round(($cancelling[0]->value+$cancelling[0]->taxes)/(1+$cancelling[0]->vat),2);
          $this->json[$pid][$this->json[$product->id]['declinations_name']][$declination->id]['prices'][$pname]['vat'] += $cancelling[0]->value + $cancelling[0]->taxes - $tep;
        }
        break;
      }
    }
    
    foreach ( $this->json as $pid => $product )
    if ( $pid )
    foreach ( $product[$product['declinations_name']] as $did => $declination )
    if ( count($declination['prices']) == 0 && count($declination['available_prices']) == 0 )
      unset($this->json[$pid][$this->json[$product->id]['declinations_name']][$gid]);
    
    $this->json = array(
      'error' => array(false, ''),
      'success' => array(
        'success_fields' => array(
          $type => array(
            'data' => array(
              'type' => $type,
              'reset' => $this->transaction ? true : false,
              'content' => $this->json,
            ),
          ),
        ),
        'error_fields' => array(),
      ),
    );

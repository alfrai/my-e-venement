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
   *     category: string, name of the category
   *     category_id: integer, the id of the category
   *     description: string, description
   *     color: string CSS color of the manifestation or the product category
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

    $this->getContext()->getConfiguration()->loadHelpers(array('Slug', 'I18N'));
    
    $fct = 'createQueryFor'.ucfirst($type);
    
    if ( $request->getParameter('id',false) )
    {
      $table = Doctrine::getTable('Transaction');
      if ( !method_exists($table, $fct) )
        $fct = 'createQuery';
      $q = Doctrine::getTable('Transaction')->$fct('t');
      if ( $type == 'store' )
        $q->andWhere('t.id = ? OR t.transaction_id = ? AND bp.integrated_at IS NOT NULL', array($request->getParameter('id'), $request->getParameter('id')))
          ->orderBy('bp.product_declination_id');
    
      else
        $q->andWhere('t.id = ?', $request->getParameter('id'));
    }
    
    switch ( $type ){
    case 'museum':
    case 'manifestations':
      $subobj = 'Ticket';
      $product_id  = 'Manifestation->id';
      $product_key = 'Manifestation->ordering_key';
      
      if ( $request->getParameter('id',false) )
      {
        $q->leftJoin('m.Event e')
          ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
          ->leftJoin('tck.Gauge g WITH g.onsite = TRUE OR tck.gauge_id IS NOT NULL AND tck.gauge_id = g.id')
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
        
        $q->leftJoin('m.IsNecessaryTo n')
          ->leftJoin('n.Gauges ng WITH ng.onsite = TRUE OR tck.gauge_id IS NOT NULL AND ng.id = tck.gauge_id')
        ;
      }
      else
        $q = Doctrine::getTable('Manifestation')->createQuery('m')
          ->leftJoin('m.IsNecessaryTo n')
          ->leftJoin('n.Gauges ng WITH ng.onsite = TRUE')
        ;
      
      // retrictive parameters (for the simplified GUI)
      $pid = array();
      if ( !$request->getParameter('manifestation_id',false)
        && !$request->getParameter('gauge_id', false)
        && $request->hasParameter('simplified') )
      {
        // here we add the next manifestations if nothing is asked and the GUI is "simplified"
        $conf = sfConfig::get('app_transaction_manifestations', array());
        if (!( isset($conf['max_display']) && is_int($conf['max_display']) ))
          $conf['max_display'] = 20;
        
        $q2 = Doctrine::getTable('Manifestation')->createQuery('m')
          ->select('m.id')
          ->andWhere('extract(epoch from NOW()) - m.duration < extract(epoch from m.happens_at)')
          ->andWhere('e.museum = ?', $type == 'museum') // differenciate museums & manifestations 
          ->orderBy('m.happens_at ASC, m.id')
          ->limit($conf['max_display']);
        $ids = array();
        foreach ( $q2->execute() as $manif )
          $ids[] = $manif->id;
        $request->setParameter('manifestation_id', $ids);
      }
      if ( $request->getParameter('manifestation_id',false) )
      {
        $pid = is_array($request->getParameter('manifestation_id'))
          ? $request->getParameter('manifestation_id')
          : array($request->getParameter('manifestation_id'));
        $expl = array();
        foreach ( $pid as $i => $n )
          $expl[] = '?';
        $q->andWhere('(TRUE')
            ->andWhereIn('m.id',$pid)
            ->orWhere('m.depends_on IN ('.implode(',',$expl).')',$pid)
          ->andWhere('TRUE)')
        ;
      }
      if ( $gid = $request->getParameter('gauge_id', false) )
        $q->andWhere('(g.id = ? OR (ng.id = ? AND g.workspace_id = ng.workspace_id))',array($gid, $gid));
      
      // ordering stuff avoids bugs w/ duplicates in some isolated cases
      $q->orderBy('m.id, g.id'); //, tck.id');
    
    break;
    case 'store':
      $subobj = 'BoughtProduct';
      $product_id  = 'Declination->product_id';
      $product_key = 'Declination->Product->ordering_key';
      
      // retrictive parameters (for the simplified GUI)
      if ( !$request->getParameter('id',false) && $request->hasParameter('simplified') )
      {
        // here we add the best products if nothing is asked and the GUI is "simplified"
        $conf = sfConfig::get('app_transaction_store', array());
        if (!( isset($conf['max_display']) && is_int($conf['max_display']) ))
          $conf['max_display'] = 20;
        
        $q2 = Doctrine::getTable('ProductDeclination')->createQuery('pd')
          ->select('pd.id')
          ->leftJoin('pd.Product p')
          ->andWhere('p.vat_id IS NOT NULL')
        ;
        if ( $request->getParameter('category_id', false) )
          $q2
            ->leftJoin('p.Category pc')
            ->andWhere('pc.id = ? OR pc.product_category_id = ?', array(intval($request->getParameter('category_id')), intval($request->getParameter('category_id'))));
        else
          $q2->limit($conf['max_display'])
            ->orderBy("(SELECT count(bp.id) FROM BoughtProduct bp WHERE bp.product_declination_id = pd.id AND bp.integrated_at > NOW() - '2 weeks'::interval) DESC, pd.created_at DESC")
          ;
         
        if ( $request->hasParameter('q') )
          $q2->andWhere('pd.code ILIKE ?', $request->getParameter('q').'%');
        
        $ids = array();
        foreach ( $q2->execute() as $pd )
          $ids[] = $pd->product_id;
        $request->setParameter('product_id', $ids);
      }
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
      {
        $q = Doctrine::getTable('Product')->createQuery('pdt');
        if ( $pid = $request->getParameter('product_id', array()) )
        {
          $pid = is_array($pid) ? $pid : array($pid);
          $q->andWhereIn('pdt.id', $pid);
        }
      }
      
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
      'gauge_url' => null,
      'description' => '',
      'id' => '',
      'ids' => array(),
      'numerotation' => array(),
    );
    
    switch ( $type ) {
      case 'store':
        $declinations_name = 'declinations';
        $product_param = 'product_id';
        $declination_param = 'declination_id';
        break;
      default:
        $declinations_name = 'gauges';
        $product_param = 'manifestation_id';
        $declination_param = 'gauge_id';
        break;
    }
    
    if ( !$this->transaction
      && !$pid
      && $request->getParameter($declination_param)
      && $request->getParameter('price_id')
    )
    {
      if ( !$request->getParameter($product_param, false) )
      {
        switch ( $type ) {
        case 'store':
          $request->setParameter($product_param, Doctrine::getTable('ProductDeclination')->createQuery('pd')->select('pd.id, pd.'.$product_param)->andWhere('pd.id = ?', $request->getParameter($declination_param))->fetchOne()->$product_param);
          break;
        default:
          $request->setParameter($product_param, Doctrine::getTable('Gauge')->createQuery('g')->select('g.id, g.'.$product_param)->andWhere('g.id = ?', $request->getParameter($declination_param))->fetchOne()->$product_param);
          break;
        }
      }
      $this->json[$request->getParameter($product_param)] = array(
        'id' => $request->getParameter($product_param),
        'declinations_name' => $declinations_name,
        $declinations_name => array(),
      );
      $this->json[$request->getParameter($product_param)][$declinations_name][$request->getParameter($declination_param)] = array(
        'id' => $request->getParameter($declination_param),
        'prices' => array($request->getParameter('price_id') => array(
          'id'      => $request->getParameter('price_id'),
          'state'   => $request->getParameter('state', '') == 'false' ? '' : $request->getParameter('state', ''),
          'qty'     => 0,
        )),
        'available_prices' => array(),
      );
    }
    else
    foreach ( $this->transaction ? $this->transaction[$subobj.'s'] : $pid as $item ) // loophole
    {
      // by manifestation/product
      if ( is_object($item) )
      {
        $obj = $item;
        foreach ( explode('->', $product_id) as $field )
        if ( is_object($obj) )
          $obj = $obj->$field;
        $id = intval($obj);
        $obj = $item;
        foreach ( explode('->', $product_key) as $field )
        if ( is_object($obj) )
          $obj = $obj->$field;
        $key = $obj;
      }
      else
        $key = $id = intval($item);
      
      if ( !isset($this->json[$key]) )
      {
        switch ( $type ) {
        case 'museum':
        case 'manifestations':
          $subobj = 'Gauge';
          $manifService = $this->getContext()->getContainer()->get('manifestations_service');
          $q = $manifService->buildQuery($this->getUser()->getGuardUser(), $request->getParameter('id',0))
            ->andWhere('m.id = ?',$id);
          
          if ( $gid = $request->getParameter('gauge_id', false) )
            $q->leftJoin('m.IsNecessaryTo n')
              ->leftJoin('n.Gauges ng WITH g.onsite = TRUE OR g.id IN (SELECT ntck.gauge_id FROM Ticket ntck WHERE ntck.transaction_id = ? AND ntck.gauge_id = ng.id)', $request->getParameter('id',0))
              ->andWhere('g.id = ? OR (ng.id = ? AND ng.workspace_id = g.workspace_id)', array($gid, $gid));
          if (!( $product = $q->fetchOne() ))
            break;

          $this->json[$product->ordering_key] = array(
            'id'            => $product->id,
            'name'          => NULL,
            'category'      => (string)$product->Event,
            'category_id'   => $product->event_id,
            'description'   => $product->Event->description,
            'image_id'      => $product->Event->Picture->id,
            'image_url'     => $product->Event->Picture->getUrl(array('absolute' => true)),
            'gauge_url'     => cross_app_url_for('event', 'gauge/state?json=true&manifestation_id='.$product->id, true),
            'happens_at'    => (string)$product->happens_at,
            'ends_at'       => (string)$product->ends_at,
            'category_url'  => cross_app_url_for('event', 'event/show?id='.$product->event_id, true),
            'product_url'   => cross_app_url_for('event', 'manifestation/show?id='.$product->id,true),
            'location'      => (string)$product->Location,
            'location_url'  => cross_app_url_for('event', 'location/show?id='.$product->location_id,true),
            'color'         => (string)$product->Color,
            'declination_url'   => cross_app_url_for('event','',true),
            'declinations_name' => $declinations_name,
          );
          break;
        case 'store':
          $subobj = 'Declination';
          $q = Doctrine::getTable('Product')->createQuery('p')
            ->leftJoin('p.Category c')
            ->leftJoin('c.Translation ct WITH ct.lang = ?', $this->getUser()->getCulture())
            ->innerJoin('p.PriceProducts pp')
            ->leftJoin('pp.Price price WITH price.hide = ? AND price.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = ?)', array(false, $this->getUser()->getId()))
            ->leftJoin('price.Translation prt WITH prt.lang = ?', $this->getUser()->getCulture())
            ->orderBy("(SELECT count(bp.id) FROM BoughtProduct bp LEFT JOIN bp.Declination pd WHERE pd.product_id = p.id AND bp.integrated_at > NOW() - '2 weeks'::interval) DESC, pt.name, dt.name, prt.name")
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
          
          if ( $product )
          $this->json[$product->ordering_key] = array(
            'id'            => $product->id,
            'name'          => (string)$product,
            'category'      => (string)$product->Category,
            'category_id'   => $product->product_category_id,
            'description'   => $product->description,
            'category_url'  => cross_app_url_for('pos', 'category/show?id='.$product->product_category_id,true),
            'product_url'   => cross_app_url_for('pos', 'product/show?id='.$product->id, true),
            'color'         => (string)$product->Category->Color,
            'declinations_url'  => NULL,
            'declinations_name' => $declinations_name,
          );
          break;
        }
        
        if ( !$product )
          continue;
        
        // gauges
        if ( !$product )
          continue;
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']] = array();
        $cpt = 0;
        foreach ( $product[$subobj.'s'] as $declination )
        {
          switch ( $subobj ) {
          case 'Gauge':
            $url = cross_app_url_for('event','gauge/state?id='.$declination->id.'&json=true',true);
            break;
          case 'Declination':
            $url = cross_app_url_for('pos','product/state?id='.$declination->product_id,true);
            break;
          }
          
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id] = array(
            'id' => $declination->id,
            'sort' => $cpt,
            'name' => (string)$declination,
            'url' => $url,
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
              $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['seated_plan_url']
                = cross_app_url_for('default', 'picture/display?id='.$seated_plan->picture_id,true);
              $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['seated_plan_seats_url']
                = cross_app_url_for('event',   'seated_plan/getSeats?ticketting=true&id='.$seated_plan->id.'&gauge_id='.$declination->id.($this->transaction ? '&transaction_id='.$this->transaction->id : ''),true);
              if ( $seated_plan->ideal_width )
              $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['seated_plan_width']
                = $seated_plan->ideal_width;
            }
            break;
          case 'Declination':
            $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['code']
              = $declination->code;
            break;
          }
          
          // available prices
          $prices = array();
          switch ( $type ) {
          case 'museum':
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
            $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['available_prices'][str_pad(number_format($pp->Price->Ranks[0]->rank,5),20,'0',STR_PAD_LEFT).' || '.$pp->Price.' || '.$pp->price_id] = array(
              'id'  => $pp->price_id,
              'name'  => (string)$pp->Price,
              'description'  => $pp->Price->description,
              'value' => format_currency($pp->value,$this->getContext()->getConfiguration()->getCurrency()),
              'raw_value' => floatval($pp->value),
              'currency' => $this->getContext()->getConfiguration()->getCurrency(),
              'color' => $pp->Price->color_id ? $pp->Price->Color->color : '0'
            );
          }
          ksort($this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['available_prices']);
        }
      }
      
      if (! $item instanceof Itemable )
        continue;
      
      // by price
      $state = $declination = NULL;
      switch ( $this->json[$product->ordering_key]['declinations_name'] ) {
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
      if (!( isset($this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname])
          && count($this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids']) > 0
      ))
      {
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname] = array(
          'state' => $state,
          'name' => !$item->price_id ? $item->price_name : $item->Price->name,
          'description' => !$item->price_id ? '' : $item->Price->description,
          'item-details' => in_array($this->json[$product->ordering_key]['declinations_name'], array('gauges')), // the link to a specific place to detail the items
          'id' => $item->price_id ? $item->price_id : slugify($item->price_name),
        ) + $items_model;
      }
      switch ( $this->json[$product->ordering_key]['declinations_name'] ) {
      case 'gauges':
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids'][] = $item->id;
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids_url'][] = cross_app_url_for('tck', 'ticket/show?id='.$item->id, true);
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['numerotation'][] = $item->numerotation;
        break;
      case 'declinations':
        if ( !$item->member_card_id )
        {
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids'][] = $item->id;
          break;
        }
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids'][] = $item->member_card_id;
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids_url'][] = cross_app_url_for('rp', 'member_card/show?id='.$item->member_card_id, true);
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['numerotation'][] = '#'.$item->id;
        break;
      }
      
      // by group of tickets
      $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['qty']++;
      $real_value = $item->value;
      $tep = NULL;
      switch ( $this->json[$product->ordering_key]['declinations_name'] ){
      case 'gauges':
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['extra-taxes'] += $item->taxes;
        $real_value = $item->value + $item->taxes;
        break;
      case 'declinations':
        $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['extra-taxes'] += $item->shipping_fees;
        $real_value = $item->value + $item->shipping_fees;
        $tep = round($item->value/(1+$item->vat),2) + round($item->shipping_fees/(1+$item->shipping_fees_vat),2);
        break;
      }
      if (!( isset($tep) && !is_null($tep) ))
        $tep = round($real_value/(1+$item->vat),2);
      $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['pit'] += $item->value;
      $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['tep'] += $tep;
      $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['vat'] += $real_value - $tep;
      
      // POST PROD SPECIFICITIES
      switch ( $this->json[$product->ordering_key]['declinations_name'] ) {
      case 'gauges':
        // cancelling tickets
        if ( $cancelling = $item->hasBeenCancelled() )
        {
          $state = 'cancelling';
          $pname = $item->price_id.'-'.$state;
          if (!( isset($this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname])
            && count($this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids']) > 0
          ))
          {
            $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname] = array(
              'state' => $state,
              'name' => !$item->price_id ? $item->price_name : $item->Price->name,
              'description' => !$item->price_id ? '' : $item->Price->description,
              'gauge_url' => cross_app_url_for('event', 'gauge/state?json=true&manifestation_id='.$product->id, true),
              'item-details' => false,
              'id' => $item->price_id ? $item->price_id : slugify($item->price_name),
            ) + $items_model;
          }
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['ids'][] = $cancelling[0]->id;
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['numerotation'][] = $cancelling[0]->numerotation;
          
          // by group of tickets
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['qty']--;
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['extra-taxes'] += $cancelling[0]->taxes;
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['pit'] += $cancelling[0]->value;
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['tep'] += $tep = round(($cancelling[0]->value+$cancelling[0]->taxes)/(1+$cancelling[0]->vat),2);
          $this->json[$product->ordering_key][$this->json[$product->ordering_key]['declinations_name']][$declination->id]['prices'][$pname]['vat'] += $cancelling[0]->value + $cancelling[0]->taxes - $tep;
        }
        break;
      }
    }
    
    foreach ( $this->json as $pid => $product )
    if ( $pid )
    foreach ( $product[$product['declinations_name']] as $did => $declination )
    if ( count($declination['prices']) == 0 && count($declination['available_prices']) == 0 )
      unset($this->json[$pid][$this->json[$pid]['declinations_name']][$gid]);
    elseif ( is_array($this->json[$pid][$this->json[$pid]['declinations_name']][$did]['prices']) )
      ksort($this->json[$pid][$this->json[$pid]['declinations_name']][$did]['prices']);
    
    ksort($this->json);
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

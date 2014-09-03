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
   * function executeGetStore
   * @param sfWebRequest $request, given by the framework (required: id, optional: Array|int product_id || (price_id, gauge_id, printed))
   * @return ''
   * @display a json array containing :
   * json:
   * error:
   *   0: boolean true if errorful, false else
   *   1: string explanation
   * success:
   *   success_fields:
   *     store:
   *       data:
   *         type: products
   *         reset: boolean
   *         content: Array (see below)
   *   error_fields: only if any error happens
   *     products: string explanation
   *
   * the data Array is :
   *   [product_id]: integer
   *     id: integer
   *     name: string
   *     category: string
   *     category_url: xxx (absolute) link
   *     product_url:  xxx (absolute) link
   *     description: string, description
   *     color: string CSS color of the product, usually NULL
   *     declinations:
   *       [declination_id]: integer
   *         name: xxx
   *         id: integer
   *         description: string, description
   *         available_prices:
   *           [id]: integer
   *             id: integer
   *             name: string, short name
   *             description: string, description
   *             value: string, contextualized price w/ currency (for the current product)
   *             raw_value: float, contextualized price w/o currency
   *             currency: string, currency
   *         prices:
   *           [price_id]:
   *             id: integer
   *             state: enum(NULL, 'integrated')
   *             qty: integer, the quantity of declinations
   *             pit: float, the price including taxes
   *             vat: float, the current VAT value
   *             tep: float, the price excluding taxes
   *             name: string, the price's name
   *             description: string, the price's description
   *             [ids]:
   *               lines' ids
   **/

    $this->getContext()->getConfiguration()->loadHelpers('Slug');
    if ( $request->getParameter('id',false) )
    {
      $q = Doctrine::getTable('Transaction')->createQueryForPos('t', $this->getUser()->getCulture())
        ->andWhere('t.id = ?', $request->getParameter('id'))
        ->leftJoin('pdt.MetaEvent me')
        ->andWhereIn('me.id', array_keys($this->getUser()->getMetaEventsCredentials()))
      ;
      
      // retrictive parameters
      if ( $pid = $request->getParameter('price_id', false) )
        $q->andWhere('bp.price_id = ? OR bp.price_id IS NULL',$pid);
      if ( $request->hasParameter('state') )
      {
        switch ( $request->getParameter('state') ){
        case 'integrated':
          $q->andWhere('tck.integrated_at IS NOT NULL');
          break;
        default:
          $q->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL');
          break;
        }
      }
    }
    elseif ( $request->getParameter('product_id',false) )
      $q = Doctrine::getTable('Product')->createQuery('pdt');
    
    $pid = array();
    if ( $request->getParameter('product_id',false) )
      $pid = is_array($request->getParameter('product_id'))
        ? $request->getParameter('product_id')
        : array($request->getParameter('product_id'));
    
    // retrictive parameters
    if ( $request->getParameter('product_id',false) )
      $q->andWhereIn('pdt.id',$pid);
    if ( $did = $request->getParameter('declination_id', false) )
      $q->andWhere('d.id = ?', $did);
    
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
      'name' => '',
      'description' => '',
      'id' => '',
      'ids' => array(),
    );
    
    foreach ( $this->transaction ? $this->transaction->BoughtProducts : $pid as $item ) // loophole
    {
      // by manifestation
      if ( !isset($this->json[$pid = $item instanceof BoughtProduct ? $item->product_id : $item]) )
      {
        $product = Doctrine::getTable('Product')->createQuery('p')
          ->leftJoin('p.Category c')
          ->leftJoin('c.Translation ct WITH ct.lang = ?', $this->getUser()->getCulture())
          ->leftJoin('p.PriceProducts pp')
          ->leftJoin('pp.Price price WITH price.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = ?)', $this->getUser()->getId())
          ->orderBy('pt.name, dt.name, price.name')
          ->leftJoin('price.UserPrices pup WITH pup.sf_guard_user_id = ?',$this->getUser()->getId())
          ->leftJoin('p.MetaEvent pme')
          ->andWhereIn('pme.id IS NULL OR pme.id', array_keys($this->getUser()->getMetaEventsCredentials()))
          ->andWhere('p.id = ?',$pid)
          ->fetchOne();
        
        $this->json[$product->id] = array(
          'id'            => $product->id,
          'name'          => (string)$product,
          'category'      => (string)$product->Category,
          'description'   => $product->description,
          'category_url'  => cross_app_url_for('pos', 'category/show?id='.$product->product_category_id,true),
          'product_url'   => cross_app_url_for('pos', 'product/show?id='.$product->id, true),
          'color'         => NULL,
        );
        
        // gauges
        $this->json[$product->id]['declinations'] = array();
        foreach ( $product->Declinations as $declination )
        {
          $this->json[$product->id]['declinations'][$declination->id] = array(
            'id' => $declination->id,
            'name' => (string)$declination,
            'description' => $declination->description,
            'available_prices' => array(),
            'prices' => array('-' => items_model),
          );
          
          // available prices
          foreach ( $product->PriceProducts as $pp )
          {
            // access to this meta event
            if ( !in_array($product->meta_event_id, array_keys($this->getUser()->getMetaEventsCredentials())) )
              continue;
            
            // then add the price...
            $this->json[$product->id]['declinations'][$declination->id]['available_prices'][] = array(
              'id'  => $pp->price_id,
              'name'  => (string)$pp->Price,
              'description'  => $pp->Price->description,
              'value' => format_currency($pp->value,'€'),
              'raw_value' => floatval($pp->value),
              'currency'  => '€',
            );
          }
        }
      }
      
      if (! $item instanceof BoughtProduct )
        continue;
      
      // by price
      $state = NULL;
      if ( $item->integrated_at )
        $state = 'integrated';
      
      $pname = $item->price_id.'-'.$state;
      if (!( isset($this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname])
          && count($this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname]['ids']) > 0
      ))
        $this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname] = array(
          'state' => $state,
          'name' => !$item->price_id ? $item->price_name : $item->Price->name,
          'description' => !$item->price_id ? '' : $item->Price->description,
          'id' => $item->price_id ? $item->price_id : slugify($item->price_name),
        ) + items_model;
      $this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname]['ids'][] = $item->id;
      
      // by group of tickets
      $this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname]['qty']++;
      $this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname]['pit'] += $item->value;
      $this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname]['tep'] += $tep = round(($item->value)/(1+$item->vat),2);
      $this->json[$item->Declination->product_id]['declinations'][$item->declination_id]['prices'][$pname]['vat'] += $item->value - $tep;
    }
    
    foreach ( $this->json as $pid => $product )
    foreach ( $product['declinations'] as $did => $declination )
    if ( count($declination['prices']) == 0 && count($declination['available_prices']) == 0 )
      unset($this->json[$pid]['declinations'][$did]);
    
    $this->json = array(
      'error' => array(false, ''),
      'success' => array(
        'success_fields' => array(
          'store' => array(
            'data' => array(
              'type' => 'products',
              'reset' => $this->transaction ? true : false,
              'content' => $this->json,
            ),
          ),
        ),
        'error_fields' => array(),
      ),
    );

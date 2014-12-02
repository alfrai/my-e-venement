<span class="manif_prices_list">
  <?php
    // hack economizing SQL queries
    global $prices_obj, $workspaces_obj;
    if ( !isset($prices_obj) )
      $prices_obj = array();
    
    $prices = array();
    foreach ( $manif->PriceManifestations as $pm )
    {
      // economizing SQL queries
      if ( isset($prices_obj[$pm->price_id]) )
        $price = $prices_obj[$pm->price_id];
      else
      {
        $price = $pm->Price;
        $prices_obj[$pm->price_id] = $price;
      }
      
      if ( $price->UserPrices[0]->sf_guard_user_id == $sf_user->getId() )
      {
        $prices[$price->id] = array('gauges' => array(), 'price' => $price->name);
        foreach ( $price->Workspaces as $ws )
        foreach ( $ws->Gauges as $g )
        if ( $g->manifestation_id == $manif->id )
          $prices[$price->id]['gauges'][$g->id] = $g->id;
      }
    }
    echo json_encode($prices);
  ?>
</span>

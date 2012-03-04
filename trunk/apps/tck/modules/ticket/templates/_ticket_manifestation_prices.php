<span class="manif_prices_list">
  <?php
    $prices = array();
    foreach ( $manif->PriceManifestations as $price )
    {
      $prices[$price->id] = array('gauges' => array(), 'price' => $price->Price->name);
      foreach ( $price->Price->Workspaces as $ws )
      foreach ( $ws->Gauges as $g )
        $prices[$price->id]['gauges'][$g->id] = $g->id;
    }
    echo json_encode($prices);
  ?>
</span>

<span class="manif_prices_list">
  <?php
    $prices = array();
    foreach ( $manif->PriceManifestations as $price )
      $prices[$price->id] = $price->Price->name;
    echo json_encode($prices);
  ?>
</span>

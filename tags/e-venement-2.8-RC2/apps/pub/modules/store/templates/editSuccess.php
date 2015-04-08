<?php include_partial('global/ariane', array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>
<?php include_partial('show_title', array('product' => $product)) ?>
<div class="product" id="product-<?php echo $product->id ?>">
<?php include_partial('show_declinations', array(
  'product' => $product,
  'declinations' => $product->Declinations,
)) ?>
</div>
<?php include_partial('show_footer', array('product' => $product)) ?>


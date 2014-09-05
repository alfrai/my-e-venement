<?php if ( $product->picture_id ): ?>
<p class="image">
  <a href="<?php echo url_for('store/mod?product_id='.$product->id) ?>" id="ajax-init-data"></a>
  <img
    src="<?php echo url_for('picture/display?id='.$product->picture_id) ?>"
    alt="<?php echo $product ?>"
    title="<?php echo $product ?>"
    class="pub-product"
  />
</p>
<?php endif ?>
<div class="text">
  <?php echo $product->getRawValue()->description ?>
</div>


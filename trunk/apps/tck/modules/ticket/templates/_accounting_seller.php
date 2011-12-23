<div id="seller">
  <?php if ( sfConfig::has('app_seller_logo') ): ?>
  <p class="logo"><?php echo image_tag('/private/'.sfConfig::get('app_seller_logo'),'logo') ?></p>
  <?php endif ?>
  <p class="name"><?php echo sfConfig::get('app_seller_name') ?></p>
  <p class="address"><?php echo nl2br(sfConfig::get('app_seller_address')) ?></p>
  <p class="postalcode"><?php echo sfConfig::get('app_seller_postalcode') ?></p>
  <p class="city"><?php echo sfConfig::get('app_seller_city') ?></p>
  <p class="country"><?php echo sfConfig::get('app_seller_country') ?></p>
  <p class="other"><?php echo sfConfig::get('app_seller_other') ?></p>
</div>

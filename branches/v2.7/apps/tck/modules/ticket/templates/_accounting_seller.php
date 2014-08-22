<div id="seller">
  <?php if ( sfConfig::has('app_seller_logo') ): ?>
  <p class="logo">
    <?php
      $uri = sfConfig::get('sf_web_dir').'/private/'.sfConfig::get('app_seller_logo');
      $b64 = base64_encode(file_get_contents($uri));
    ?>
    <img src="data:image/jpeg;base64,<?php echo $b64 ?>" alt="logo" />
  </p>
  <?php endif ?>
  <p class="name"><?php echo sfConfig::get('app_seller_name') ?></p>
  <p class="address"><?php echo nl2br(sfConfig::get('app_seller_address')) ?></p>
  <p class="postalcode"><?php echo sfConfig::get('app_seller_postalcode') ?></p>
  <p class="city"><?php echo strtoupper(sfConfig::get('app_seller_city')) ?></p>
  <p class="country"><?php echo strtoupper(sfConfig::get('app_seller_country')) ?></p>
  <?php $translate = array(
    '%%transaction_id%%' => $transaction->id,
    '%%order_id%%' => $type == 'order' ? $transaction->Order[0]->id : '',
    '%%invoice_id%%' => $type == 'invoice' ? sfConfig::get('app_seller_invoice_prefix').$transaction->Invoice[0]->id : '',
  ) ?>
  <p class="other"><?php echo str_replace(array_keys($translate),array_values($translate),nl2br(sfConfig::get('app_seller_other'))) ?></p>
</div>

<?php $infos = sfConfig::get('app_seller_order_infos') ?>
<div id="infos">
  <?php if ( isset($infos['gcs']) ): ?>
  <div class="gcs">
    <?php echo $infos['gcs'] ?>
  </div>
  <?php endif ?>

  <?php if ( isset($infos['salutation']) ): ?>
  <div class="salutation">
    <?php echo ($infos['salutation']) ?>
  </div>
  <?php endif ?>

  <?php if ( isset($infos['agreement']) ): ?>
  <div class="agreement">
    <?php echo $infos['agreement'] ?>
  </div>
  <?php endif ?>
</div>

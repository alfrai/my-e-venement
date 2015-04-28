<div class="declination" id="declination-<?php echo $declination->id ?>" data-declination-id="<?php echo $declination->id ?>">
  <h3><?php echo $declination ?></h3>
  <div class="text">
    <?php echo $declination->getRawValue()->description ?>
  </div>
  <?php include_partial('show_prices',array('declination' => $declination)) ?>
</div>

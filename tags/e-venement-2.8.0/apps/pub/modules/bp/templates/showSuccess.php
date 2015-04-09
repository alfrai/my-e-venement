<?php use_helper('Date') ?>
<?php include_partial('global/ariane', array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>
<?php include_partial('store/show_title', array('product' => $pdt)) ?>
<div class="bought_product" id="bp-<?php echo $pdt->id ?>">
  <h2><?php echo $pdt->declination ?></h2>
  <div class="content"><?php echo $pdt->getRawValue()->description_for_buyers ?></div>
  <div class="transaction_id">
    <?php echo format_date($pdt->integrated_at) ?>
    #<?php echo link_to($pdt->transaction_id, 'transaction/show?id='.$pdt->transaction_id) ?>
  </div>
</div>
<?php include_partial('store/show_footer', array('product' => $pdt)) ?>


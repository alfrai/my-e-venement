<?php include_partial('assets') ?>

<?php use_helper('Date') ?>
<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Selling tickets') ?></h1>
    <p style="display: none;" id="global_transaction_id"><?php echo $transaction->id ?></p>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="contact">
    <?php echo link_to('contact','ticket/contact?id='.$transaction->id) ?>
  </div>
  <div class="touchscreen ui-corner-all ui-widget-content">
    <!-- PRICES -->
    <?php include_partial('touchscreen_prices',array(
      'config' => $config,
      'transaction' => $transaction,
      'prices' => $prices,
      'remove_manifestation_list' => true,
    )) ?>
    
    <!-- MANIFESTATIONS -->
    <?php include_partial('touchscreen_manifestations', array(
      'config' => $config,
    )) ?>
    
    <!-- TICKETS -->
    <?php include_partial('touchscreen_tickets', array(
      'config' => $config,
      'transaction' => $transaction,
    )) ?>
  </div>
</div>

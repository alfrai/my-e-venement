<?php include_partial('global/flashes') ?>
<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all" id="sales-ledger">
<?php include_partial('sales_title', array('dates' => $dates)) ?>
<?php include_partial('criterias',array('form' => $form, 'ledger' => 'sales')) ?>
<?php
  $arr = array();
  foreach ( array('manifestations', 'users', 'workspaces', 'dates') as $var )
    $arr[$var] = isset($$var) ? $$var : false;
  include_partial('show_criterias',$arr);
  include_partial('sales_criterias', array('form' => $form));
?>

<table class="ui-widget-content ui-corner-all" id="ledger-events">
  <?php
    $vat = array();
    $total = $sf_data->getRaw('total');
    
    require(__DIR__.'/_sales_events_prepare.php');
  ?>
  <?php $arr = array(); ?>
  <tbody><?php foreach ( $events as $event ): ?>
    <?php
      $local_vat = $qty = $value = $taxes = 0;
      $infos = array();
      require(__DIR__.'/_sales_event_prepare.php');
    ?>
    <tr class="event">
      <?php require(__DIR__.'/_sales_event.php') ?>
    </tr>
    <?php foreach ( $event->Manifestations as $manif ): $local_vat = 0; ?>
    <tr class="manif event-<?php echo $event->id ?>">
      <?php require(__DIR__.'/_sales_manifestation.php') ?>
    </tr>
    <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ) for ( $i = 0 ; $i < $manif->Tickets->count() ; $i++ ): ?>
    <tr class="prices manif-<?php echo $manif->id ?>">
      <?php require(__DIR__.'/_sales_event_prices.php') ?>
    </tr>
    <?php endfor; endforeach; endforeach; ?>
  </tbody>
  <tfoot><tr class="total">
    <?php include_partial('sales_total', array('total' => $total,)) ?>
  </tr></tfoot>
  <thead><tr>
    <?php include_partial('sales_events_header', array('total' => $total)) ?>
  </tr></thead>
</table>

<?php use_helper('Slug'); ?>
<?php
  $vat = $pdts = array();
  $total = $sf_data->getRaw('products_total');
  require(__DIR__.'/_sales_products_prepare.php');
?>
<table class="ui-widget-content ui-corner-all" id="ledger-products">
  <tbody><?php foreach ( $pdts as $pdtname => $pdt ): ?>
    <tr class="product">
      <?php require(__DIR__.'/_sales_product.php') ?>
    </tr>
    <?php foreach ( $pdt['declinations'] as $dname => $declination ): ?>
    <tr class="declination product-<?php echo $pdt['id'] ?>">
      <?php require(__DIR__.'/_sales_declination.php') ?>
    </tr>
    <?php if ( $nb_products <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
    <?php foreach ( $declination['prices'] as $prname => $price ): ?>
    <tr class="prices declination-<?php echo slugify($dname) ?>">
      <?php require(__DIR__.'/_sales_product_prices.php') ?>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
    <?php endforeach; endforeach; ?>
  </tbody>
  <tfoot><tr class="total">
    <?php include_partial('sales_total', array('total' => $total,)) ?>
  </tr></tfoot>
  <thead><tr>
    <?php include_partial('sales_products_header', array('total' => $total)) ?>
  </tr></thead>
</table>

<div class="clear"></div>
</div>

<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>Vendre des billets</h1>
    <p style="display: none;" id="global_transaction_id"><?php echo $transaction->id ?></p>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="contact">
    <?php echo link_to('contact','ticket/contact?id='.$transaction->id) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="manifestations">
    <?php echo link_to('manifestations','ticket/manifs?id='.$transaction->id) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="prices">
    <?php include_partial('ticket_prices',array('transaction' => $transaction, 'prices' => $prices)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="print">
    <?php include_partial('ticket_print',array('transaction' => $transaction)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="payment">
    <?php include_partial('ticket_payment',array('transaction' => $transaction, 'payform' => $payform)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="validation">
    <?php include_partial('ticket_validation',array('transaction' => $transaction)) ?>
  </div>
</div>

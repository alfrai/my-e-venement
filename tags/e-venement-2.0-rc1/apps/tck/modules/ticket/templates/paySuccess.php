<?php include_partial('assets') ?>
<?php use_helper('Number') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Single cash deal') ?></h1>
    <p style="display: none;" id="global_transaction_id"><?php echo $transaction->id ?></p>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="topay">
    <?php $ticks = array(); foreach ( $transaction->Tickets as $ticket ) $ticks[$ticket->cancelling] = $ticket->value; ?>
    <strong><?php echo __('Transaction #%%id%%:',array('%%id%%' => $transaction->id)) ?></strong>
    <span id="to_pay"><?php echo format_currency(array_sum($ticks),'€') ?></span>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="print">
    <?php include_partial('ticket_print',array('transaction' => $transaction,'accounting' => false)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="payment">
    <?php include_partial('ticket_payment',array('transaction' => $transaction)) ?>
  </div>
</div>

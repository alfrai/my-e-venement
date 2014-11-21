<?php include_partial('assets') ?>
<?php use_helper('Number') ?>

<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Single cash deal') ?></h1>
    <p style="display: none;" id="global_transaction_id" class="translinked" title="#<?php $transaction->transaction_id ?>"><?php echo $transaction->id ?></p>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="topay">
    <?php $ticks = array(); foreach ( $transaction->Tickets as $ticket ) if ( is_null($ticket->duplicating) ) $ticks[$transaction->type == 'cancelling' ? $ticket->cancelling : $ticket->id] = $ticket->value + $ticket->taxes; ?>
    <strong class="translinked"  title="#<?php echo $transaction->transaction_id ?>"><?php echo __('Transaction #%%id%%:',array('%%id%%' => $transaction->id)) ?></strong>
    <span id="to_pay"><?php echo format_currency(array_sum($ticks),'€') ?></span>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="tickets">
    <?php include_partial('ticket_show',array('transaction' => $transaction,)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="print">
    <?php include_partial('ticket_print',array('transaction' => $transaction, 'accounting' => true, 'display_simple' => true,)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="payment">
    <?php include_partial('ticket_payment',array('transaction' => $transaction)) ?>
  </div>
  <form class="ui-corner-all ui-widget-content action" id="close" action="<?php echo url_for('ticket/cancel') ?>" method="get">
    <p>
      <input type="submit" name="" value="<?php echo __('Close') ?>" onclick="javascript: window.close()" />
    </p>
  </form>
</div>

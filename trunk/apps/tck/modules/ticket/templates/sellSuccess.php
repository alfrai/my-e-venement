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
  <form class="ui-corner-all ui-widget-content description" id="description" action="#" method="get">
    <textarea name="transaction[description]"><?php echo $transaction->description ?></textarea>
    <input type="hidden" name="transaction[_csrf_token]" value="<?php $f = new sfForm; echo $f->getCSRFToken() ?>" />
  </form>
  <div id="transaction-id"
    class="<?php echo $transaction->Translinked->count() > 0 ? 'translinked' : '' ?>"
    title="<?php $arr = array(); foreach ( $transaction->Translinked as $trans ) $arr[] = '#'.$trans->id.' ('.__($trans->type).')'; echo implode(', ',$arr); ?>">
    <?php echo __('Transaction #%%id%%',array('%%id%%' => $transaction->id)) ?>
    <span>(<?php echo __('updated on %%d%% by %%u%%',array('%%d%%' => format_datetime($transaction->updated_at), '%%u%%' => $transaction->User)) ?>)</span>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="manifestations">
    <?php echo link_to('manifestations','ticket/manifs?id='.$transaction->id) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="prices">
    <?php include_partial('ticket_prices',array('transaction' => $transaction, 'prices' => $prices)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="print">
    <?php if ( $sf_user->hasCredential('tck-print-ticket') ) include_partial('ticket_print',array('transaction' => $transaction,'accounting' => true)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="payment">
    <?php if ( $sf_user->hasCredential('tck-payment') ) include_partial('ticket_payment',array('transaction' => $transaction, 'payform' => $payform)) ?>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="validation">
    <?php include_partial('ticket_validation',array('transaction' => $transaction)) ?>
  </div>
</div>

<div id="min-height"></div>
<?php include_partial('accounting_assets',array('modifiable' => true)) ?>
<?php include_partial('accounting_place') ?>
<?php include_partial('accounting_date') ?>
<?php include_partial('accounting_type_invoice') ?>
<?php include_partial('accounting_seller',array('transaction' => $transaction, 'type' => 'invoice')) ?>
<?php include_partial('accounting_customer',array('transaction' => $transaction)) ?>
<?php include_partial('accounting_ids_invoice',array('transaction' => $transaction,'invoice' => $invoice)) ?>
<?php include_partial('accounting_lines',array('transaction' => $transaction,'tickets' => $tickets,'nocancel' => $nocancel,)) ?>
<?php include_partial('accounting_totals',array('totals' => $totals)) ?>
<?php if ( !$partial ): ?>
<?php include_partial('accounting_payments',array('transaction' => $transaction,'nocancel' => $nocancel,)) ?>
<?php endif ?>
<?php include_partial('accounting_footer') ?>
<p class="extra-infos"><em><?php echo __($nocancel ? 'This invoice excludes cancelled tickets' : '') ?></em></p>

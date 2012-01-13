<?php include_partial('accounting_assets') ?>
<?php include_partial('accounting_date') ?>
<?php include_partial('accounting_type_invoice') ?>
<?php include_partial('accounting_seller') ?>
<?php include_partial('accounting_customer',array('transaction' => $transaction)) ?>
<?php include_partial('accounting_ids_invoice',array('transaction' => $transaction,'invoice' => $invoice)) ?>
<?php include_partial('accounting_lines',array('transaction' => $transaction,'tickets' => $tickets,'nocancel' => $nocancel,)) ?>
<?php include_partial('accounting_totals',array('totals' => $totals)) ?>
<?php include_partial('accounting_footer') ?>

<?php include_partial('accounting_assets') ?>
<?php include_partial('accounting_date') ?>
<?php include_partial('accounting_type_order') ?>
<?php include_partial('accounting_seller') ?>
<?php include_partial('accounting_customer',array('transaction' => $transaction)) ?>
<?php include_partial('accounting_ids_order',array('transaction' => $transaction,'order' => $order)) ?>
<?php include_partial('accounting_lines',array('transaction' => $transaction)) ?>
<?php include_partial('accounting_totals',array('totals' => $totals)) ?>

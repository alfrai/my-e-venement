<?php include_partial('accounting_assets') ?>
<?php include_partial('accounting_date') ?>
<?php include_partial('accounting_type_order') ?>
<?php include_partial('accounting_seller',array('transaction' => $transaction, 'type' => 'order')) ?>
<?php include_partial('accounting_customer',array('transaction' => $transaction)) ?>
<?php include_partial('accounting_ids_order',array('transaction' => $transaction,'order' => $order)) ?>
<?php include_partial('accounting_lines',array('transaction' => $transaction,'tickets' => $tickets)) ?>
<?php include_partial('accounting_totals',array('totals' => $totals)) ?>

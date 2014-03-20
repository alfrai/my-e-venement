<?php include_partial('accounting_assets', array('modifiable' => true)) ?>
<?php include_partial('accounting_place') ?>
<?php include_partial('accounting_date') ?>
<?php include_partial('accounting_type_order') ?>
<?php include_partial('accounting_seller',array('transaction' => $transaction, 'type' => 'order')) ?>
<?php include_partial('accounting_customer',array('transaction' => $transaction)) ?>
<?php include_partial('accounting_ids_order',array('transaction' => $transaction,'order' => $order)) ?>
<?php include_partial('accounting_lines',array('transaction' => $transaction,'tickets' => $tickets, 'nocancel' => $nocancel)) ?>
<?php include_partial('accounting_totals',array('totals' => $totals)) ?>
<?php include_partial('order_infos') ?>
<?php include_partial('accounting_footer') ?>


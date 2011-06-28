<?php use_javascript('jquery','first') ?>
<?php use_stylesheet('ledger-both') ?>
<?php use_helper('Number'); ?>
<div class="ledger-both">
<?php include_partial('both_payment',array('byPaymentMethod' => $byPaymentMethod)) ?>
<?php include_partial('both_price',array('byPrice' => $byPrice)) ?>
<?php include_partial('both_value',array('byValue' => $byValue)) ?>
<?php include_partial('both_user',array('byUser' => $byUser)) ?>
</div>

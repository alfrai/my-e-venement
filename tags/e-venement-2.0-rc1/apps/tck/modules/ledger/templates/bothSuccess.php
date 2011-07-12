<?php include_partial('assets') ?>
<?php use_stylesheet('ledger-both','',array('media' => 'all')) ?>

<div class="ledger-both">
<?php include_partial('criterias',array('form' => $form, 'ledger' => 'both')) ?>
<?php include_partial('both_payment',array('byPaymentMethod' => $byPaymentMethod)) ?>
<?php include_partial('both_price',array('byPrice' => $byPrice)) ?>
<div class="clear"></div>
<?php include_partial('both_value',array('byValue' => $byValue)) ?>
<?php include_partial('both_user',array('byUser' => $byUser)) ?>
</div>

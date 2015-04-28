<?php use_helper('Number') ?>
<?php $amount = $web_origin->Transaction->getPrice(true,true) ?>
<?php echo $amount ? format_currency($amount,'€') : '-' ?>

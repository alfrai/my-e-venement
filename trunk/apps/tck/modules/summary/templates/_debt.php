<?php use_helper('Number') ?>
<span title="<?php echo __('Price to pay') ?>" class="price"><?php echo format_currency($price = $transaction->getPrice(),'€') ?></span>
-
<span title="<?php echo __('Amount already paid') ?>" class="paid"><?php echo format_currency($paid = $transaction->getPaid(),'€') ?></span>
=
<span title="<?php echo __('Total') ?>" class="debt"><?php echo format_currency($price - $paid,'€') ?></span>

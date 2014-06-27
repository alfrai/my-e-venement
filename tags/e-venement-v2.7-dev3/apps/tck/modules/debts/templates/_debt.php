<?php use_helper('Number') ?>
<span title="<?php echo __('Price to pay') ?>" class="price"><?php echo format_currency($price = $transaction->outcomes,'€') ?></span>
-
<span title="<?php echo __('Amount already paid') ?>" class="paid"><?php echo format_currency($paid = $transaction->incomes,'€') ?></span>
=
<span title="<?php echo __('Total') ?>" class="debt"><?php echo format_currency($price - $paid,'€') ?></span>

<?php if ( $transaction->BoughtProducts->count() > 0 ): ?>
<?php $val = 0; foreach ( $transaction->BoughtProducts as $bp ) $val += $bp->value; ?>
<span class="label"><?php echo __('Products') ?>:</span>
<span class="nb"><?php echo $transaction->BoughtProducts->count() ?></span>
<span class="total"><?php echo format_currency($val,'€') ?></span>
<?php endif ?>

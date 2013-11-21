<div id="totals">
  <p class="tep">
    <span><span><?php echo __('Total excl. tax:') ?></span></span>
    <span class="float"><?php echo format_currency(round($totals['tip'] - $totals['vat']['total'],2),'€') ?></span>
  </p>
  <?php
    foreach ( $totals['vat'] as $key => $value )
    if ( $key != 'total' && $value != 0 ):
  ?>
  <p class="vat">
    <span><?php echo __('VAT %%p%%:',array('%%p%%' => ($key*100).'%')) ?></span>
    <span class="float"><?php echo format_currency(round($value,2),'€') ?></span>
  </p>
  <?php endif ?>
  <p class="pit">
    <span><span><?php echo __('Total incl. taxes:') ?></span></span>
    <span class="float"><?php echo format_currency(round($totals['tip'],2),'€') ?></span>
  </p>
</div>

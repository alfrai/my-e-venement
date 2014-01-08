<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
)) ?>
<?php echo $form ?>
<span class="confirmation"><?php echo __('Do you really want to exit this transaction?') ?></span>
<ul class="overbooking">
  <li class="msg block"><?php echo __('The blinking quantities mean that those gauges are or will be full.') ?><br/><?php echo PHP_EOL ?><?php echo __('You cannot proceed your action though.') ?></li>
  <li class="msg warn"><?php echo __('The blinking quantities mean that those gauges are or will be full.') ?><br/><?php echo PHP_EOL ?><?php echo __('Please confirm that you allow this overbooking.') ?></li>
  <li class="type" data-type="<?php echo sfConfig::get('app_transaction_gauge_block') && !$sf_user->hasCredential('tck-admin') ? 'block' : 'warn' ?>"></li>
</ul>
</form>

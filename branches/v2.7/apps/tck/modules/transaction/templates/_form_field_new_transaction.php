<?php if ( sfConfig::get('app_transaction_persistent_manifestations',true) ): ?>
<a href="<?php echo url_for('transaction/new') ?>"
   class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button persistant"
   title="<?php echo __('New transaction')."\n".__('With the same selection') ?>">
  <span class="ui-icon ui-icon-circle-arrow-e"></span>
</a>
<?php endif ?>
<a href="<?php echo url_for('transaction/new') ?>"
   class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
   title="<?php echo __('New transaction') ?>">
  <span class="ui-icon ui-icon-circle-plus"></span>
</a>

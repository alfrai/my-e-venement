<a href="<?php echo url_for('transaction/new') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button <?php echo sfConfig::get('app_transaction_persistent_manifestations',true) ? 'persistant' : '' ?>" title="<?php echo __('New transaction') ?>">
  <span class="ui-icon ui-icon-circle-plus"></span>
</a>

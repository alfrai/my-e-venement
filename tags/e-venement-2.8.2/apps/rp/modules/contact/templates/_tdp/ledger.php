<?php if ( !$object->getRawValue() instanceof Contact ) return ?>
<a target="_blank"
  title="<?php echo __('Sales ledger') ?>"
   href="<?php echo cross_app_url_for('tck', 'ledger/sales?'.strtolower(get_class($object->getRawValue())).'_id='.$object->id) ?>"
   class="ledger ledger-sales fg-button-mini fg-button ui-state-default fg-button-icon-left"
>
  <span class="ui-icon ui-icon-clipboard"></span>
</a>

<?php $object = isset($contact) ? $contact : $professional ?>
<a target="_blank"
   href="<?php echo cross_app_url_for('tck', 'transaction/new?'.strtolower(get_class($object->getRawValue())).'_id='.$object->id) ?>"
   class="new-transaction fg-button-mini fg-button ui-state-default fg-button-icon-left"
>
  <span class="ui-icon ui-icon-cart"></span>
</a>

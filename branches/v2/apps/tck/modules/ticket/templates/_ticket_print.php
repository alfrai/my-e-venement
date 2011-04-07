<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>" method="get" target="_blank">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" />
    <input type="checkbox" name="duplicate" value="true" />
    <input type="text" name="price_name" value="" class="price" />
  </p>
</form>

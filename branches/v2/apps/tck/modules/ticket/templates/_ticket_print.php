<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>" method="get" target="_blank">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" />
    <input type="checkbox" name="duplicate" value="true" />
    <input type="text" name="price_name" value="" class="price" />
  </p>
</form>
<form action="<?php echo url_for('ticket/order?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting">
  <p>
    <?php
      $order_id = 0;
      foreach ( $transaction->Accountings as $accounting )
      if ( $accounting->type == 'order' )
        $order_id = $accounting->id;
    ?>
    <input type="submit" name="cancel-order" value="<?php echo __('Cancel order') ?>" <?php if ( !$order_id ): ?>style="display: none;"<?php endif ?> />
    <input type="submit" name="order" value="<?php echo __('Order') ?>" />
  </p>
</form>
<form action="<?php echo url_for('ticket/invoice?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting">
  <p><input type="submit" name="invoice" value="<?php echo __('Invoice') ?>" /></p>
</form>

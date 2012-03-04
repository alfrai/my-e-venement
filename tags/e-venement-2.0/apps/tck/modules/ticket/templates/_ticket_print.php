<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>" method="get" target="_blank" class="print">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" onclick="javascript: if ( $('#manifestations #force-alert').length > 0 && $('.manifestations_list .alert').length > 0 ) return confirm('<?php echo __('Some gauges are full, are you sure?') ?>');" />
    <?php if ( isset($accounting) && $accounting !== false ): ?>
    <input type="checkbox" name="duplicate" value="true" title="<?php echo __('Duplicatas') ?>" />
    <input type="text" name="price_name" value="" class="price" />
    <?php endif ?>
  </p>
</form>
<?php if ( $sf_user->hasCredential('tck-integrate') ): ?>
<form action="<?php echo url_for('ticket/integrate?id='.$transaction->id) ?>" method="get" target="_blank" class="integrate">
  <p>
    <input type="submit" name="s" value="<?php echo __('Integrate') ?>" title="<?php echo __("Integrate from an external seller") ?>" />
  </p>
</form>
<?php endif ?>
<?php if ( isset($accounting) && $accounting !== false ): ?>
<?php if ( $sf_user->hasCredential('tck-accounting-order') ): ?>
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
<?php endif ?>
<?php if ( $sf_user->hasCredential('tck-accounting-invoice') ): ?>
<form action="<?php echo url_for('ticket/invoice?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting">
  <p>
    <input type="submit" name="invoice" value="<?php echo __('Invoice') ?>" />
    <input type="checkbox" name="partial" value="partial" title="<?php echo __("Generate an invoice focused only on the selected manifestation.") ?>" />
    <input type="checkbox" name="nocancel" value="nocancel" title="<?php echo __("Excludes cancelled tickets from invoice.") ?>" />
  </p>
</form>
<?php endif ?>
<?php endif ?>
<form action="<?php echo url_for('ticket/partial?id='.$transaction->id) ?>" method="get" target="_blank" class="partial">
  <script type="text/javascript">
    $(document).ready(function(){
      $('#print form.partial').submit(function(){
        if ( $('.manifestations_list [name="ticket[manifestation_id]"]:checked').length > 0 )
          $(this).find('[name=manifestation_id]').val($('.manifestations_list [name="ticket[manifestation_id]"]:checked').val());
        else
        {
          alert('<?php echo __('You must have at least one manifestation selected.') ?>');
          return false;
        }
      });
    });
  </script>
  <p>
    <input type="submit" value="<?php echo __('Partial printing') ?>" name="partial" title="<?php echo __('Only on the selected line/manifestation') ?>" />
    <input type="hidden" name="manifestation_id" value="" />
  </p>
</form>

<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>" method="get" target="_blank" class="print">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" onclick="javascript: return click_print()" class="ui-widget-content ui-state-default ui-corner-all ui-widget" />
    <script type="text/javascript"><!--
    function click_print()
    {
      if ( $('#manifestations #force-alert').length > 0 && $('.manifestations_list .alert').length > 0 )
      {
        if ( <?php echo sfConfig::get('app_transaction_gauge_block') ? 'true' : 'false' ?> )
        {
          if ( <?php echo !$sf_user->hasCredential('tck-admin') ? 'true' : 'false' ?> )
          {
            alert('<?php echo __('Some gauges are full, check them out.') ?>');
            return false;
          }
          else
            return confirm('<?php echo __('Some gauges are full, are you sure?') ?>');
        }
        else
          return confirm('<?php echo __('Some gauges are full, are you sure?') ?>');
      }
    }
    --></script>
    <?php if ( sfConfig::has('app_tickets_authorize_grouped_tickets') && sfConfig::get('app_tickets_authorize_grouped_tickets') ): ?>
    <input type="checkbox" name="grouped_tickets" value="true" title="<?php echo __('Grouped tickets') ?>" />
    <?php endif ?>
    <?php if ( isset($accounting) && $accounting !== false ): ?>
    <input type="checkbox" name="duplicate" value="true" title="<?php echo __('Duplicatas') ?>" />
    <input type="text" name="price_name" value="" class="price" />
    <?php endif ?>
  </p>
</form>
<?php if ( $sf_user->hasCredential('tck-integrate') ): ?>
<form action="<?php echo url_for('ticket/integrate?id='.$transaction->id) ?>" method="get" target="_blank" class="integrate">
  <p>
    <input type="submit" name="s" value="<?php echo __('Integrate') ?>" title="<?php echo __("Integrate from an external seller") ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget" />
  </p>
</form>
<?php endif ?>
<?php if ( sfConfig::get('app_transaction_force_contact') && !$sf_user->hasCredential('tck-admin') ): ?>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#print .integrate input[type=submit], #print .print input[type=submit]').click(function(){
      if ( !$('.contact input[name="transaction[contact_id]"]').val() )
      {
        alert("<?php echo __('You forgot to specify a contact... or to press ENTER to validate it.') ?>");
        return false;
      }
    });
  });
--></script>
<?php endif ?>
<?php if ( isset($accounting) && $accounting !== false ): ?>
<?php if ( $sf_user->hasCredential('tck-accounting-order') ): ?>
<form action="<?php echo url_for('ticket/order?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting">
  <p>
    <?php $has_order = $transaction->Order->count() > 0 ? true : false ?>
    <input type="submit" name="cancel-order" value="<?php echo __('Cancel order') ?>" <?php if ( !$has_order ): ?>style="display: none;"<?php endif ?> class="ui-widget-content ui-state-default ui-corner-all ui-widget" />
    <input type="submit" name="order" value="<?php echo __('Order') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget" />
    <input type="checkbox" name="nocancel" value="nocancel" title="<?php echo __("Excludes cancelled tickets from order.") ?>" />
  </p>
</form>
<?php endif ?>
<?php if ( $sf_user->hasCredential('tck-accounting-invoice') ): ?>
<form action="<?php echo url_for('ticket/invoice?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting">
  <p>
    <input type="submit" name="invoice" value="<?php echo __('Invoice') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget" />
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
    <input type="submit" value="<?php echo __('Partial printing') ?>" name="partial" title="<?php echo __('Only on the selected line/manifestation') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget" />
    <input type="hidden" name="manifestation_id" value="" />
  </p>
</form>

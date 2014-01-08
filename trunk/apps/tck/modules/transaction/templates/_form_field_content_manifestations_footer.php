<div class="ui-corner-all ui-widget-content">
<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>" method="get" target="_blank" class="print noajax" onsubmit="javascript: return li.checkGauges(this);">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
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
<form action="<?php echo url_for('ticket/integrate?id='.$transaction->id) ?>" method="get" target="_blank" class="integrate noajax"  onsubmit="javascript: return li.checkGauges(this);">
  <p>
    <input type="submit" name="s" value="<?php echo __('Integrate') ?>" title="<?php echo __("Integrate from an external seller") ?>" class="ui-widget-content ui-state-default fg-button ui-corner-all ui-widget" />
  </p>
</form>
<?php endif ?>
<form action="<?php echo url_for('ticket/partial?id='.$transaction->id) ?>" method="get" target="_blank" class="partial noajax">
  <script type="text/javascript">
    $(document).ready(function(){
      $('#li_transaction_manifestations .footer .partial').submit(function(){
        if ( $('#li_transaction_manifestations .ui-state-highlight').length == 0 )
        {
           alert("<?php echo __('You must have at least one manifestation selected.') ?>");
           $(this).find('[name=manifestation_id]').val('');
           return false;
        }
        
        $(this).find('[name=manifestation_id]').val($('#li_transaction_field_content .ui-state-highlight').attr('data-gauge-id'));
        return li.checkGauges(this);
      });
    });
  </script>
  <p>
    <input type="submit" value="<?php echo __('Partial printing') ?>" name="partial" title="<?php echo __('Only on the selected line/manifestation') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
    <input type="hidden" name="manifestation_id" value="" />
  </p>
</form>
</div>

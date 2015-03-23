<?php use_javascript('tck-touchscreen-print') ?>
<div class="ui-corner-all ui-widget-content">
<?php if ( $sf_user->hasCredential('tck-print-ticket') ): ?>

<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>"
      method="get"
      target="_blank" class="print noajax board-alpha"
      onsubmit="javascript: return LI.printTickets(this,<?php echo sfConfig::get('app_transaction_force_payment_before_printing',false) ? 'true' : 'false' ?>);"
      autocomplete="off">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
    <?php if ( sfConfig::has('app_tickets_authorize_grouped_tickets') && sfConfig::get('app_tickets_authorize_grouped_tickets') ): ?>
    <input type="checkbox" name="grouped_tickets" value="true" title="<?php echo __('Grouped tickets') ?>" />
    <?php endif ?>
    <input type="checkbox" name="duplicate" value="true" title="<?php echo __('Duplicatas') ?>" onclick="javascript: if ( $('#li_transaction_manifestations .item.ui-state-highlight').length == 0 ) { $(this).prop('checked',false); LI.alert($(this).closest('form').find('.choose-a-manifestation').text()); return false; } $(this).hide(); $(this).closest('form').find('[name=price_name]').show().focus();" />
    <input type="text" name="price_name" value="" title="<?php echo __('Duplicatas') ?>" class="price" size="5" style="display: none;" />
    <input type="hidden" name="manifestation_id" value="" />
    <span style="display: none;" class="choose-a-manifestation"><?php echo __('You must choose a manifestation first') ?></span>
  </p>
</form>

<?php if ( $sf_user->hasCredential('tck-integrate') ): ?>
<form action="<?php echo url_for('ticket/integrate?id='.$transaction->id) ?>"
      method="get" target="_blank" class="integrate noajax"
      onsubmit="javascript: return LI.checkGauges(this);">
  <p>
    <input type="submit" name="s"
           value="<?php echo __('Integrate') ?>"
           title="<?php echo __("Integrate from an external seller") ?>"
           class="ui-widget-content ui-state-default fg-button ui-corner-all ui-widget"
    />
  </p>
</form>
<?php endif ?>

<form action="<?php echo url_for('ticket/partial?id='.$transaction->id) ?>"
      method="get"
      target="_blank"
      class="partial noajax">
  <p>
    <input type="submit" value="<?php echo __('Partial printing') ?>" name="partial" title="<?php echo __('Only on the selected line/manifestation') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
    <input type="hidden" name="gauge_id" value="" />
  </p>
</form>

<?php endif ?>
</div>


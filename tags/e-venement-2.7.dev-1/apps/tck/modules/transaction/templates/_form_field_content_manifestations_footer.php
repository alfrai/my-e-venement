<div class="ui-corner-all ui-widget-content">
<form action="<?php echo url_for('ticket/print?id='.$transaction->id) ?>"
      method="get"
      target="_blank" class="print noajax board-alpha"
      onsubmit="javascript: return li.printTickets(this);"
      autocomplete="off">
  <p>
    <input type="submit" name="s" value="<?php echo __('Print') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
    <?php if ( sfConfig::has('app_tickets_authorize_grouped_tickets') && sfConfig::get('app_tickets_authorize_grouped_tickets') ): ?>
    <input type="checkbox" name="grouped_tickets" value="true" title="<?php echo __('Grouped tickets') ?>" />
    <?php endif ?>
    <input type="checkbox" name="duplicate" value="true" title="<?php echo __('Duplicatas') ?>" onclick="javascript: if ( $('#li_transaction_manifestations .item.ui-state-highlight').length == 0 ) { $(this).prop('checked',false); li.alert($(this).closest('form').find('.choose-a-manifestation').text()); return false; } $(this).hide(); $(this).closest('form').find('[name=price_name]').show().focus();" />
    <input type="text" name="price_name" value="" title="<?php echo __('Duplicatas') ?>" class="price" size="5" style="display: none;" />
    <input type="hidden" name="manifestation_id" value="" />
    <span style="display: none;" class="choose-a-manifestation"><?php echo __('You must choose a manifestation first') ?></span>
  </p>
  <script type="text/javascript">
    li.resetDuplicates = function(form){
      $(form).find('[name=price_name]').val('').hide();
      $(form).find('[name=duplicate]').prop('checked',false).show();
      $(form).find('[name=manifestation_id]').val('');
    }
    li.printTickets = function(form){
      if ( $('#li_transaction_manifestations .item.ui-state-highlight').length == 0
        && $(form).find('[name=manifestation_id]').prop('checked') )
      {
        $(form).focusout();
        return li.checkGauges(form);
      }
      if ( $(form).find('[name=duplicate]').prop('checked') && $(form).find('[name=price_name]').val() )
        $(form).find('[name=manifestation_id]').val($('#li_transaction_manifestations .item.ui-state-highlight').closest('.family').attr('data-family-id'));
      setTimeout(function(){ $('#li_transaction_manifestations .footer .print [name=price_name]').val('').blur(); }, 2500);
      return li.checkGauges(form);
    }
    $(document).ready(function(){
      // dealing w/ the text field that aims to define the price_name to duplicate
      $('#li_transaction_manifestations .footer .print [name=price_name]').focusin(function(){
        $('#li_transaction_field_board').addClass('alpha');
      }).focusout(function(){
        if ( !$(this).val() )
        $('#li_transaction_field_board').removeClass('alpha');
      }).click(function(){
        if ( !$(this).val() ) $(this).val(' ');
      }).blur(function(){
        if ( $(this).val() )
          return;
        li.resetDuplicates($(this).closest('form'));
      });
    });
  </script>
</form>
<?php if ( $sf_user->hasCredential('tck-integrate') ): ?>
<form action="<?php echo url_for('ticket/integrate?id='.$transaction->id) ?>"
      method="get" target="_blank" class="integrate noajax"
      onsubmit="javascript: return li.checkGauges(this);">
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

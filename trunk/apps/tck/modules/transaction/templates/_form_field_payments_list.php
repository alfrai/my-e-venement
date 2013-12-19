<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
<table>
<tbody>
  <?php if ( $transaction->Payments->count() == 0 ): ?>
  <tr class="sf_admin_row ui-widget-content">
    <td align="center" height="30" colspan="3">
      <p align="center"><?php echo __('No result', null, 'sf_admin') ?></p>
    </td>
    <td colspan="2"></td>
  </tr>
  <?php else: ?>
  <?php foreach ( $transaction->Payments as $i => $payment ): ?>
  <tr class="sf_admin_row ui-widget-content<?php echo $i % 2 == 0 ? '' : ' odd' ?>">
    <td style="display: none;">
      <input name="ids[]" value="<?php echo $payment->id ?>" class="sf_admin_batch_checkbox" type="checkbox" />
    </td>
    <td class="sf_admin_text sf_admin_list_td_Method"><?php echo $payment->Method ?></td>
    <td class="sf_admin_text sf_admin_list_td_list_created_at" colspan="2"><?php echo format_date($payment->created_at) ?></td>
    <td class="sf_admin_text sf_admin_list_td_list_value"><?php echo format_currency($payment->value,'€') ?></td>
    <td style="white-space: nowrap;">
      <ul class="sf_admin_td_actions fg-buttonset fg-buttonset-single">
        <li class="sf_admin_action_delete">
          <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left ui-priority-secondary" href="/e-venement/web/tck_dev.php/payment/199"><span class="ui-icon ui-icon-trash"></span>Supprimer</a>
        </li>
      </ul>
    </td>
  </tr>
  <?php endforeach ?>
  <?php endif ?>
</tbody>
<tfoot>
  <tr class="sf_admin_row ui-widget-content odd total">
    <td class="sf_admin_text" colspan="3"><?php echo __('Total') ?></td>
    <td class="sf_admin_text sf_admin_list_td_list_value pit" title="<?php echo __('Total') ?>"></td>
    <td></td>
  </tr>
  <tr class="sf_admin_row ui-widget-content odd topay">
    <td class="sf_admin_text"><?php echo __('To pay') ?></td>
    <td class="sf_admin_text sf_admin_list_td_list_value tep" title="<?php echo __('PET') ?>"></td>
    <td class="sf_admin_text sf_admin_list_td_list_value vat" title="<?php echo __('VAT') ?>"></td>
    <td class="sf_admin_text sf_admin_list_td_list_value pit" title="<?php echo __('Total') ?>"></td>
    <td></td>
  </tr>
  <tr class="sf_admin_row ui-widget-content odd change">
    <td class="sf_admin_text"><?php echo __('Still missing') ?></td>
    <td class="sf_admin_text sf_admin_list_td_list_value tep" title="<?php echo __('PET') ?>"></td>
    <td class="sf_admin_text sf_admin_list_td_list_value vat" title="<?php echo __('VAT') ?>"></td>
    <td class="sf_admin_text sf_admin_list_td_list_value pit" title="<?php echo __('Total') ?>"></td>
    <td></td>
  </tr>
</tfoot>
</table>
<script type="text/javascript">
$(document).ready(function(){
  $('#li_transaction_field_payments_list tr').unbind('click');
  $('#li_transaction_field_payments_list tfoot tr')
    .mouseenter(function(){ $(this).addClass('ui-state-hover'); })
    .mouseleave(function(){ $(this).removeClass('ui-state-hover'); });
  li.sumPayments();
});
li.sumPayments = function()
{
  var val = 0;
  $('#li_transaction_field_payments_list tbody tr .sf_admin_list_td_list_value').each(function(){
    val += isNaN(parseFloat($(this).html(),10))
      ? 0
      : parseFloat($(this).html(),10);
  });
  $('#li_transaction_field_payments_list tfoot .total .sf_admin_list_td_list_value')
    .html(li.format_currency(val));
}
</script>
</div>

<div class="footer">
<?php if ( $sf_user->hasCredential('tck-accounting-order') ): ?>
<form action="<?php echo url_for('ticket/order?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting order noajax">
  <p>
    <input type="submit" name="order" value="<?php echo __('Order') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
    <input type="checkbox" name="nocancel" value="nocancel" title="<?php echo __("Excludes cancelled tickets from order.") ?>" />
    <input type="submit" name="cancel-order" value="<?php echo __('Cancel order') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
  </p>
</form>
<?php endif ?>
<?php if ( $sf_user->hasCredential('tck-accounting-invoice') ): ?>
<form action="<?php echo url_for('ticket/invoice?id='.$transaction->id) ?>" method="get" target="_blank" class="accounting invoice noajax">
  <p>
    <input type="checkbox" name="nocancel" value="nocancel" title="<?php echo __("Excludes cancelled tickets from invoice.") ?>" />
    <input type="checkbox" name="partial" value="" title="<?php echo __("Generate an invoice focused only on the selected manifestation.") ?>" />
    <input type="submit" name="invoice" value="<?php echo __('Invoice') ?>" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" />
  </p>
  <script type="text/javascript">
    $('#li_transaction_field_payments_list .accounting').submit(function(){
      if ( $(this).find('[name=partial]:checked').length > 0 )
      {
        if ( $('#li_transaction_field_content .families .ui-state-highlight').length > 0 )
          $(this).find('[name=partial]:checked').val($('#li_transaction_field_content .families .ui-state-highlight').closest('.family').attr('data-family-id'));
        else
          $(this).find('[name=partial]:checked').prop('checked', false);
      }
    });
    
    <?php if ( $transaction->Order->count() == 0 ): ?>
      $('#li_transaction_field_payments_list .accounting.order [name=cancel-order]')
        .css('visibility','hidden');
    <?php endif ?>
    $('#li_transaction_field_payments_list .accounting.order').submit(function(){
      $('#li_transaction_field_payments_list .accounting.order [name=cancel-order]')
        .css('visibility', 'visible');
    });
    $('#li_transaction_field_payments_list .accounting.order [name=cancel-order]').click(function(){
      $.ajax({
        url:  $(this).closest('form').prop('action')+'?cancel-order',
        method: 'get',
        complete: function(){
          $('#li_transaction_field_payments_list .accounting.order [name=cancel-order]')
            .css('visibility', 'hidden');
        }
      });
      return false;
    });
  </script>
</form>
<?php endif ?>
</div>

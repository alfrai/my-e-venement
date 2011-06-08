<script type="text/javascript">
  function ticket_payment_form(data)
  {
    // adding the form
    $(data).find('.sf_admin_form form').prependTo('#payment');
    $('#payment form #payment_transaction_id').val('<?php echo $transaction->id ?>');
    $('#payment form').addClass('ui-widget-content ui-corner-all').append('<p><input type="submit" name="submit" value="<?php echo __('Add') ?>" /><input type="hidden" name="_save_and_add" value="" /></p>');
    $('#payment form .sf_admin_actions_block').remove();
    $('#payment form .label > *').each(function(){
      var tmp = $(this).parent();
      tmp.parent().prepend($(this));
      tmp.remove();
    });
    
    // adding shortcuts
    var shortcuts = $('<p></p>');
    $('#payment form #payment_payment_method_id option').each(function(){
      if ( $(this).val() != '' )
      {
        var content = $(this).html()
          .replace(/\//g,' ')
          .replace(/\s*([a-zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])[a-zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ/]*/ig,"$1.").toUpperCase();
        shortcuts.append('<button name="'+$('#payment #payment_payment_method_id').attr('name')+'" value="'+$(this).val()+'" title="'+$(this).html()+'">'+content+'</button>');
      }
    });
    shortcuts.find('button').click(function(){
      $('#payment #payment_payment_method_id').val($(this).val());
      $('#payment #payment_value').val(parseFloat($('#payment .sf_admin_list .change .sf_admin_list_td_list_value').html()));
      $('#payment form').submit();
      return false;
    });
    $('#payment form').append(shortcuts);
    
    // ajax'ing the form
    $('#payment form').submit(function(){
      $.post($(this).attr('action'),$(this).serialize(),function(data){
        $('#payment form').remove();
        ticket_payment_form(data);
        ticket_payment_old();
      });
      return false;
    });
  }

  function ticket_payment_old()
  {
    $('#payment .sf_admin_list').remove();
    $.get('<?php echo url_for('payment/index?transaction_id='.$transaction->id) ?>',function(data){
      $(data).find('.sf_admin_list')
        .appendTo('#payment')
        .find('thead, tfoot, caption, .sf_admin_action_show, .sf_admin_action_edit').remove();
      if ( $('#payment td:first-child input[type=checkbox]').length > 0 )
        $('#payment td:first-child').remove();
      delete_code = $('#payment .sf_admin_action_delete a').each(function(){
        delete_code = $(this).attr('onclick');
        delete_code = (delete_code+"").replace('f.submit();',"f.target='_delete';f.submit();delwin=window.open('','_delete');delwin.close();ticket_payment_old();");
        $(this).removeAttr('onclick');
        eval(delete_code);
        $(this).click(onclick);
      });
      
      var pay_total = 0;
      var currency = '&nbsp;€'; //$('#prices .total .total').html().replace("\n",'').replace(/^\s*\d+[,\.]\d+/g,'');
      $('#payment tbody td:first-child + td').each(function(){
        pay_total += parseFloat($(this).html().replace(',','.'));
      });
      $('#payment tbody')
        .append('<tr class="sf_admin_row ui-widget-content odd total"><td class="sf_admin_text"><?php echo __('Total') ?></td><td class="sf_admin_text sf_admin_list_td_list_value">'+pay_total.toFixed(2)+currency+'</td><td></td></tr>')
        .append('<tr class="sf_admin_row ui-widget-content odd topay"><td class="sf_admin_text"><?php echo __('To pay') ?></td><td class="sf_admin_text sf_admin_list_td_list_value"></td><td></td></tr>')
        .append('<tr class="sf_admin_row ui-widget-content odd change"><td class="sf_admin_text"><?php echo __('Still missing') ?></td><td class="sf_admin_text sf_admin_list_td_list_value"></td><td></td></tr>');
      ticket_process_amount();
    });
  }

  $(document).ready(function(){
    // new
    $.get('<?php echo url_for('payment/new') ?>',ticket_payment_form);
    
    // olds
    ticket_payment_old();
  });
</script>

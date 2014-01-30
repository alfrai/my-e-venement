    li.resetDuplicates = function(form){
      $(form).find('[name=price_name]').val('').hide();
      $(form).find('[name=duplicate]').prop('checked',false).show();
      $(form).find('[name=manifestation_id]').val('');
    }
    
    li.printTickets = function(form, pay_before = false){
      if ( pay_before && parseFloat($('#li_transaction_field_payments_list .change .pit').html()) > 0 )
      {
        li.alert($('#li_transaction_field_close .print .pay-before').html());
        return false;
      }
      
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
      
      // partial printing
      $('#li_transaction_manifestations .footer .partial').submit(function(){
        if ( $('#li_transaction_manifestations .ui-state-highlight').length == 0 )
        {
           alert($('#li_transaction_field_close .print .pay-before').html());
           $(this).find('[name=manifestation_id]').val('');
           return false;
        }
        
        $(this).find('[name=manifestation_id]').val($('#li_transaction_field_content .ui-state-highlight').attr('data-gauge-id'));
        return li.checkGauges(this);
      });
    });

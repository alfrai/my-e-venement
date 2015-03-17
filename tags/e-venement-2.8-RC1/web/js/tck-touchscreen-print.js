    LI.resetDuplicates = function(form){
      $(form).find('[name=price_name]').val('').hide();
      $(form).find('[name=duplicate]').prop('checked',false).show();
      $(form).find('[name=manifestation_id]').val('');
    }
    
    LI.printTickets = function(form, pay_before = false){
      if ( pay_before && LI.parseFloat($('#li_transaction_field_payments_list .change .pit').html()) > 0 )
      {
        LI.alert($('#li_transaction_field_close .print .pay-before').html());
        return false;
      }
      
      if ( $('#li_transaction_manifestations .item.ui-state-highlight').length == 0
        && $(form).find('[name=manifestation_id]').prop('checked') )
      {
        $(form).focusout();
        return LI.checkGauges(form);
      }
      
      // work around Work In Progress tickets (w/o a given price, but a seat only)
      var go = true;
      $('#li_transaction_manifestations .families:not(.sample) .family:not(.total) .declination').each(function(){
        if ( parseInt($(this).attr('data-price-id'))+'' !== ''+$(this).attr('data-price-id') && $(this).find('.qty input').val() != '0' )
          go = false;
      });
      if ( !go )
      {
        LI.alert($('#li_transaction_field_close .print .give-price-to-wip').text());
        return false;
      }
      
      if ( $(form).find('[name=duplicate]').prop('checked') && $(form).find('[name=price_name]').val() )
        $(form).find('[name=manifestation_id]').val($('#li_transaction_manifestations .item.ui-state-highlight').closest('.family').attr('data-family-id'));
      setTimeout(function(){ $('#li_transaction_manifestations .footer .print [name=price_name]').val('').blur(); }, 2500);
      return LI.checkGauges(form);
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
        LI.resetDuplicates($(this).closest('form'));
      });
      
      // partial printing
      $('#li_transaction_manifestations .footer .partial').submit(function(){
        if ( $('#li_transaction_manifestations .ui-state-highlight').length == 0 )
        {
           LI.alert($('#li_transaction_field_close .print .partial-print-error').html());
           $(this).find('[name=manifestation_id]').val('');
           return false;
        }
        
        if ( $('#li_transaction_field_content .ui-state-highlight[data-gauge-id]').length > 0 )
        {
          $(this).find('[name=gauge_id]').val($('#li_transaction_field_content .ui-state-highlight').attr('data-gauge-id'));
          if ( !LI.checkGauges(this) )
            return false;
          
          // refresh the gauge, as soon as the focus is back on the transaction
          $(window).focus(function(){
            LI.initContent();
            $(this).unbind('focus');
          });
        }
        return true;
      });
    });

// ALL FORMS VALIDATION
LI.formSubmit = function(){
  var form = this;
  if ( $(form).hasClass('noajax') )
    return true;
  
  $.ajax({
    url: $(form).prop('action'),
    data: $(form).serialize(),
    type: $(form).prop('method'),
    success: function(data){
      if ( data.error == undefined )
      { 
        console.log('No data... '+$(form).prop('action')+' ? '+$(form).serialize());
        return;
      }
      
      // main error
      if ( data.error[0] )
      {
        LI.alert(data.error[1],'error');
        setTimeout(function(){
          if ( data.error[2] && data.error[3] && data.error[4] && confirm(data.error[2]) )
          {
            var input = $(form).find('[name="transaction['+data.error[3]+']['+data.error[4]+']"]')
              .val(1);
            $(form).submit();
            input.val(null);
          }
        },750);
        return;
      }
      
      // detailed errors
      var msg = '';
      $.each(data.success.error_fields, function( index, value ){
        msg += index+': '+value+"\n";
      });
      if ( msg ) LI.alert(msg,'error');
      
      // successes
      $.each(data.success.success_fields, function(index, value){
        var elt = '#li_'+data.base_model+'_field_'+index;
        var remote_content = $(elt).find('.data').length > 0 && value.remote_content != undefined;
        
        $(elt).find('.data').remove();
        $(elt).append('<div class="data"></div>');
        
        // external calls / hook
        if ( LI.touchscreenFormComplete !== undefined )
        $.each(LI.touchscreenFormComplete, function(i, fct){
          fct(value, index);
        });
        
        // if link
        if ( remote_content && value.remote_content.url != undefined && value.remote_content.text != undefined )
        {
          $('<a></a>').prop('href', value.remote_content.url).prop('target', '_blank')
            .html(value.remote_content.text)
            .appendTo($(elt).find('.data'));
        }
        
        // any data to play with
        if ( value.data && value.data.type )
        switch ( value.data.type ) {
        case 'seat':
            $(value.data.tickets).each(function(){
                var ticket = this;
                $('#li_transaction_field_content .item[data-gauge-id='+ticket.gauge_id+'] .ticket-data .not-seated[data-id='+ticket.id+']')
                    .addClass('seated')
                    .append("\n"+ticket.seat_name)
                ;
            });
            break;
        case 'manifestations_price':
        case 'museum_price':
        case 'store_price':
          $('#li_transaction_field_price_new [name="transaction[price_new][qty]"]').val('');
          LI.checkAutoseatable($('#li_transaction_field_content .item.highlight'));
          if ( !value.data.reset )
            return;
          
          var elt = $(str = '[data-'+value.data.content['data-attr']+'='+value.data.content.declination_id+'] .declination'+(value.data.content.state ? '.active.'+value.data.content.state : ':not(.active)')+'[data-price-id='+value.data.content.price_id+']');
          if ( value.data.content.qty > 0 )
          {
            elt.find('.qty input').val(value.data.content.qty).select();
            elt.closest('.item').find('.total').select();
          }
          else
          {
            elt.find('.qty input').val(value.data.content.qty).select();
            elt.find('.money').html(LI.format_currency(0));
            setTimeout(function(){ if ( parseInt(elt.find('.qty input').val(),10) == 0 ) elt.remove(); },3500);
          }
          
          break;
        case 'store':
        case 'manifestations':
        case 'museum':
          LI.completeContent(value.data.content, value.data.type, false);
          break;
        
        case 'gift_coupon':
          LI.alert(value.data.alert, 'success');
          $('#li_transaction_field_gift_coupon input[type=text]').val('');
          $('#li_transaction_field_payment_new [name="transaction[payment_new][member_card_id]"]').remove();
          
          var mcid = $('<input type="hidden">')
          .prop('name', 'transaction[payment_new][member_card_id]')
          .val(value.data.id);
          
          $('<p class="field_mc"></p>').append(mcid)
            .appendTo($('#li_transaction_field_payment_new form'));
          break;
        case 'choose_mc':
          $('#li_transaction_field_payment_new [name="transaction[payment_new][member_card_id]"]').remove();
          var select = $('<select></select>').append('<option></option>')
            .prop('name', 'transaction[payment_new][member_card_id]')
            .change(function(){
              $('#li_transaction_field_payment_new [name="transaction[payment_new][payment_method_id]"][value='+$(this).attr('data-payment-id')+']')
                .parent().find('button').click();
            })
          ;
          //for ( i = 0 ; i < value.data.content.length ; i++ )
          $.each(value.data.content, function(i, mc){
            if ( mc === Object(mc) )
              $('<option></option>').val(mc.id).html(mc.name)
                .appendTo(select);
            else
              select.attr('data-payment-id', mc);
          });
          $('<p class="field_mc"></p>').append(select)
            .appendTo($('#li_transaction_field_payment_new form'));
          break;
        }
        
        // any select's options to add
        if ( value.remote_content && value.remote_content.load )
        switch ( value.remote_content.load.type ) {
        case 'manifestations_price':
        case 'museum_price':
        case 'store_price':
          var reset = value.remote_content.load.reset;
          $.each([LI.urls['manifestations'], LI.urls['museum'], LI.urls['store']], function(id, url) {
            $.ajax({
              url: url,
              complete: function(data){
                form.pending = undefined;
                $(form).find('[name="transaction[price_new][state]"]').val('');
              },
              success: function(data){
                if ( data.error[0] ) { LI.alert(data.error[1],'error'); return; }
                if (!( data.success.error_fields !== undefined && data.success.error_fields.manifestations === undefined )) { LI.alert(data.success.error_fields.manifestations,'error'); return; }
                
                $.each(data.success.success_fields, function(type, obj) {
                  if ( data.success.success_fields[type] !== undefined && data.success.success_fields[type].data !== undefined )
                    LI.completeContent(data.success.success_fields[type].data.content, type, reset);
                });
              }
            })
          });
          break;
        case 'payments':
          $('#li_transaction_field_payment_new [name="transaction[payment_new][member_card_id]"]').remove();
          $('#li_transaction_field_payment_new [name="transaction[payment_new][value]"]').val('').focus();
          $('#li_transaction_field_payment_new [name="transaction[payment_new][detail]"]').val('');
          $.ajax({
            url: value.remote_content.load.url,
            success: function(data){
              if ( data.error[0] ) { LI.alert(data.error[1],'error'); return; }
              if (!( data.success.error_fields !== undefined && data.success.error_fields.payments === undefined )) { LI.alert(data.success.error_fields.payments,'error'); return; }
              if ( data.success.success_fields.payments !== undefined && data.success.success_fields.payments.data !== undefined )
                LI.completeContent(data.success.success_fields.payments.data.content, 'payments');
            }
          });
        break;
        case 'options':
          var select = value.remote_content.load.target ? $(value.remote_content.load.target) : $(form).find('select:first');
          
          if ( value.remote_content.load.reset ) // reset
            select.find('option:not(:first-child)').remove();
          
          if ( value.remote_content.load.data ) // complete
          $.each(value.remote_content.load.data, function(index, value){
            $('<option />').val(index).html(value)
              .appendTo(select);
          });
          
          // default val
          if ( value.remote_content.load.default )
            select.val(value.remote_content.load.default);
          
          // init an other widget
          var sel = value.remote_content.load.target.replace(/^(.*)\s.*$/, '$1');
          if ( sel != elt ) LI.initTouchscreen(sel);
          
          break;
          case 'member_card':
            $('#member_card__csrf_token').val($('.store-mc-print').attr('data-token'));
            $('#member_card_contact_id').val($('#transaction_contact_id').val());
            $('#member_card_member_card_type_id').val(value.remote_content.load.data.member_card_type_id);
            $('.store-mc-print').unbind('submit').submit();
            break;
        }
        
        LI.initTouchscreen(elt);
      });
    }
  });
  
  // debug purposes
  if ( location.hash === '#debug' )
    return true;
  
  return false;
}

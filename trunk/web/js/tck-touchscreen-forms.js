// ALL FORMS VALIDATION
li.formSubmit = function(){
  var form = this;
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
        li.alert(data.error[1],'error');
        return;
      }
      
      // detailed errors
      var msg = '';
      $.each(data.success.error_fields, function( index, value ){
        msg += index+': '+value+"\n";
      });
      if ( msg ) li.alert(msg,'error');
      
      // successes
      $.each(data.success.success_fields, function(index, value){
        var elt = '#li_'+data.base_model+'_field_'+index;
        var remote_content = $(elt).find('.data').length > 0 && value.remote_content != undefined;
        
        $(elt).find('.data').remove();
        $(elt).append('<div class="data"></div>');
        
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
        case 'gauge_price':
          if ( !value.data.reset )
            return;
          
          elt = $(str = '#li_transaction_item_'+value.data.content.gauge_id+' .declination'+(value.data.printed ? '.printed' : ':not(.printed)')+'[data-price-id='+value.data.content.price_id+']');
          if ( value.data.content.qty > 0 )
          {
            elt.find('.qty input').val(value.data.content.qty).select();
            elt.closest('.item').find('.total').select();
          }
          else
            elt.remove();
          
          break;
        case 'manifestations':
          li.completeContent(value.data.content, 'manifestations', false);
          break;
        }
        
        // any select's options to add
        if ( value.remote_content && value.remote_content.load )
        switch ( value.remote_content.load.type ) {
        case 'gauge_price':
          $.ajax({
            url: value.remote_content.load.url,
            complete: function(data){ form.pending = undefined; },
            success: function(data){
              if ( data.error[0] ) { li.alert(data.error[1],'error'); return; }
              if (!( data.success.error_fields !== undefined && data.success.error_fields.manifestations === undefined )) { li.alert(data.success.error_fields.manifestations,'error'); return; }
              if ( data.success.success_fields.manifestations !== undefined && data.success.success_fields.manifestations.data !== undefined )
                li.completeContent(data.success.success_fields.manifestations.data.content, 'manifestations', false);
            }
          });
          break;
        case 'payments':
          $.ajax({
            url: value.remote_content.load.url,
            success: function(data){
              if ( data.error[0] ) { li.alert(data.error[1],'error'); return; }
              if (!( data.success.error_fields !== undefined && data.success.error_fields.payments === undefined )) { li.alert(data.success.error_fields.payments,'error'); return; }
              if ( data.success.success_fields.payments !== undefined && data.success.success_fields.payments.data !== undefined )
                li.completeContent(data.success.success_fields.payments.data.content, 'payments');
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
          if ( sel != elt ) li.initTouchscreen(sel);
          
          break;
        }
        
        li.initTouchscreen(elt);
      });
    }
  });
  
  // debug purposes
  if ( location.hash === '#debug' )
    return true;
  
  return false;
}

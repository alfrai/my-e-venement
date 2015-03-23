LI.completeContentTriggers = [];
LI.completeContent = function(data, type, replaceAll = true)
{
  if ( typeof data != 'object' )
  {
    LI.alert('An error occured. Please try again.','error');
    return;
  }
  
  // PAYMENTS
  if ( type == 'payments' )
  {
    var content = $('#li_transaction_field_payments_list tbody');
    var template = content.find('tr.template');
    
    content.find('tr[data-payment-id]:not(.template)').remove();
    content.find('tr:not([data-payment-id])').show();
    
    if ( data.length == 0 )
    {
      LI.sumPayments();
      return false;
    }
    
    content.find('tr:not([data-payment-id])').hide();
    var total = 0;

    $.each(data, function(index, value){
      var tr = template.clone(true).removeClass('template');
      
      tr.find('[name="ids[]"]').val(value.id);
      tr.attr('data-payment-id', value.id);
      tr.find('.sf_admin_list_td_Method').html(value.method);
      tr.find('.sf_admin_list_td_list_value').html(LI.format_currency(parseFloat(value.value)));
      if ( value.delete_url )
      {
        tr.find('.sf_admin_td_actions .sf_admin_action_delete form').prop('action', value.delete_url);
        tr.find('.sf_admin_td_actions .sf_admin_action_delete a').prop('href', '#'+value.id);
      }
      else
        tr.find('.sf_admin_td_actions .sf_admin_action_delete').remove();
      tr.find('.sf_admin_td_actions .sf_admin_action_delete [name="transaction[payments_list][id]"]').val(value.id);
      
      var date = new Date(value.date.replace(' ','T'));
      tr.find('.sf_admin_list_td_list_created_at').html(date.toLocaleDateString());
      
      if ( value.translinked )
      {
        tr.addClass('cancellation');
        tr.prop('title', $('#li_transaction_field_close .payments .translinked').text().replace('%%id%%', value.translinked));
      }
      
      tr.appendTo(content);
      total += value.value;
    });
    
    LI.sumPayments();
    return true;
  }
  
  // MANIFESTATIONS
  var wglobal = $('#li_transaction_'+type+' .families:not(.sample)'); // first element, parent of all
  
  if ( replaceAll )
  {
    wglobal = $('#li_transaction_'+type+' .families.sample').clone(true)
      .removeClass('sample');
    $('#li_transaction_'+type+' .families:not(.sample)').remove();
    wglobal.find('.family:not(.total)').remove();
    wglobal.insertBefore($('#li_transaction_'+type+' .footer'));
  }
  
  // manifestations
  $.each(data, function(id, manifestation){
    
    var wmanif = $('#li_transaction_'+type+' .families.sample .family:not(.total)').clone(true);
    var add = true;
    if ( $('#'+wmanif.prop('id')+manifestation.id).length > 0 )
    {
      wmanif = $('#'+wmanif.prop('id')+manifestation.id);
      add = false;
    }
    else
    {
      wmanif.prop('id', wmanif.prop('id')+manifestation.id);
      wmanif.attr('data-family-id', manifestation.id);
      wmanif.find('.item:not(.total)').remove();
    }
    
    happens_at = new Date(manifestation.happens_at.replace(' ','T'));
    ends_at = new Date(manifestation.ends_at.replace(' ','T'));
    
    // in progress: manifestation
    wmanif.find('h3 .event').text(manifestation.name).prop('href',manifestation.event_url);
    wmanif.find('h3 .happens_at').text(happens_at.toLocaleString().replace(/:\d\d \w+$/,'')).prop('href',manifestation.manifestation_url).prop('title', ends_at.toLocaleString().replace(/:\d\d \w+$/,''));
    wmanif.find('h3 .location').text(manifestation.location).prop('href',manifestation.location_url);
    wmanif.find('h3').css('background-color', manifestation.color);
    // TODO (or not): gauge_url
    
    if ( add )
      wmanif.insertBefore(wglobal.find('.family.total'));
    
    // gauges
    $.each(manifestation.gauges, function(index, gauge){
      var wgauge = $('#li_transaction_'+type+' .families.sample .item:not(.total)').clone(true);
      var add = true;
      if ( $('#'+wgauge.prop('id')+gauge.id).length > 0 )
      {
        wgauge = $('#'+wgauge.prop('id')+gauge.id);
        add = false;
      }
      else
      {
        wgauge.find('.declination').remove();
        wgauge.attr('data-gauge-id', gauge.id);
        wgauge.prop('id', wgauge.prop('id')+gauge.id);
      }
      
      wgauge.find('h4').text(gauge.name);
      
      // prices
      wgauge.find('.data .available_prices').remove();
      $('<span></span>').addClass('available_prices').html(JSON.stringify(gauge.available_prices))
        .appendTo(wgauge.find('.data'));
      
      // graphical gauges
      wgauge.find('.data .gauge.raw').remove();
      $('<a></a>')
        .prop('href', gauge.url)
        .addClass('gauge').addClass('raw')
        .appendTo(wgauge.find('.data'));
      if ( gauge.seated_plan_url && gauge.seated_plan_seats_url )
      {
        wgauge.find('.data .gauge.seated:not(.picture)').remove();
        $('<a></a>').addClass('gauge').addClass('seated')
          .prop('href', gauge.seated_plan_seats_url)
          .append($('<img/>').prop('src', gauge.seated_plan_url).prop('alt', 'seated-plan'))
          .appendTo(wgauge.find('.data'));
      }
      
      if ( add )
        wgauge.insertBefore(wmanif.find('.item.total'));
      
      // in progress: prices
      if ( gauge['prices'] != undefined )
      $.each(gauge['prices'], function(index, price){
        var wprice = $('#li_transaction_'+type+' .families.sample .declination').clone(true);
        var add = true;
        if ( (tmp = wgauge.find(str = '[data-price-id='+price.id+'].declination'+(price.state ? '.active.'+price.state : ':not(.active)'))).length > 0 )
        {
          wprice = tmp;
          add = false;
          wprice.find('.qty input').val(price.qty).select();
        }
        
        // check if price is available for this user
        var mod = false;
        $.each(gauge.available_prices, function(k, p){
          if ( p.id === price.id )
            mod = true;
        });
        if ( !mod || price.state )
        {
          wprice.addClass('active').addClass(price.state ? price.state : 'readonly');
          if ( $.inArray(price.state, ['printed', 'cancelling']) > -1 )
            wprice.find('.qty input').prop('readonly', true);
        }
        wprice.find('.qty input').val(price.qty).select();
        wprice.find('.price_name').html(price.name).prop('title', price.description);
        wprice.find('.pit').html(LI.format_currency(price.pit));
        wprice.find('.vat').html(LI.format_currency(price.vat));
        wprice.find('.tep').html(LI.format_currency(price.tep));
        wprice.attr('data-price-id', price.id);
        
        // ids & numerotation
        var ids = [];
        $.each(price.ids, function(index, value){
          ids.push(value+( price.numerotation[index] ? ' '+price.numerotation[index] : '' ));
        });
        wprice.find('.ids').html('#'+ids.join(', #'));
        
        if ( add )
          wprice.appendTo(wgauge.find('.declinations tbody'));
      }); // each bunch of tickets
    }); // each gauge
  }); // each manifestation
  
  $('#li_transaction_'+type+' .item .total').select();
  
  if ( typeof LI.completeContentTriggers == 'object' )
  {
    $.each(LI.completeContentTriggers, function(id, fct){
      fct(type, data);
    });
  }
}

LI.sumPayments = function()
{
  var val = 0;
  $('#li_transaction_field_payments_list tbody tr .sf_admin_list_td_list_value').each(function(){
    val += isNaN(parseFloat($(this).html().replace(',','.')))
      ? 0
      : LI.parseFloat($(this).html());
  });
  $('#li_transaction_field_payments_list tfoot .total .sf_admin_list_td_list_value')
    .html(LI.format_currency(val));
  
  var ratio = val / LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.pit').html());
  if ( isNaN(ratio) )
    ratio = 0;
  
  // difference
  $('#li_transaction_field_payments_list tfoot .change .sf_admin_list_td_list_value.pit').html(LI.format_currency(
    LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.pit').html())
    - val
  ));
  
  // VAT & co.
  var topay = LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.pit').html());
  $('#li_transaction_field_payments_list tfoot .change .sf_admin_list_td_list_value.vat').html(LI.format_currency(
    LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.vat').html())
    * ratio
  ));
  $('#li_transaction_field_payments_list tfoot .change .sf_admin_list_td_list_value.tep').html(LI.format_currency(
    LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.tep').html())
    * ratio
  ));
}

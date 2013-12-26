li.completeContent = function(data, type, replaceAll = true)
{
  if ( typeof data != 'object' )
  {
    alert("<?php echo __('An error occured. Please try again.') ?>");
    return;
  }
  
  // PAYMENTS
  if ( type == 'payments' )
  {
    if ( data.length == 0 )
      return false;
    
    var content = $('#li_transaction_field_payments_list tbody');
    var template = content.find('tr.template');
    content.find('tr:not(.template)').remove();
    
    $.each(data, function(index, value){
      var tr = template.clone(true).removeClass('template');
      
      tr.find('[name="ids[]"]').val(value.id);
      tr.find('.sf_admin_list_td_Method').html(value.method);
      tr.find('.sf_admin_list_td_list_value').html(li.format_currency(parseFloat(value.value)));
      tr.find('.sf_admin_td_actions .sf_admin_action_delete a').prop('href', value.delete_url);
      
      var date = new Date(value.date.replace(' ','T'));
      tr.find('.sf_admin_list_td_list_created_at').html(date.toLocaleDateString());
      
      tr.prependTo(content);
    });
    
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
    // TODO: gauge_url
    
    if ( add )
      wmanif.insertBefore(wglobal.find('.family.total'));
    
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
      
      infos = {
        id: gauge.id,
        available_prices: gauge.available_prices,
        gauge_url: gauge.url,
        seated_plan_url: gauge.seated_plan_url,
        seated_plan_seats_url: gauge.seated_plan_seats_url,
      };
      wgauge.find('.infos').html(JSON.stringify(infos));
      // TODO: gauge_url
      // TODO: seated_plan_url
      // TODO: seated_plan_seats_url
      
      if ( add )
        wgauge.insertBefore(wmanif.find('.item.total'));
      
      // in progress: prices
      if ( gauge['prices'] != undefined )
      $.each(gauge['prices'], function(index, price){
        var wprice = $('#li_transaction_'+type+' .families.sample .declination').clone(true);
        var add = true;
        if ( (tmp = wgauge.find(str = '[data-price-id='+price.id+'].declination'+(price.printed ? '.printed' : ':not(.printed)'))).length > 0 )
        {
          wprice = tmp;
          add = false;
          wprice.find('.qty input').val(price.qty).select();
        }
        
        //wprice.addClass(price.cancelling ? 'cancelling' : '');
        if ( price.printed )
        {
          wprice.addClass('printed');
          wprice.find('.qty input').prop('readonly', true);
        }
        wprice.find('.qty input').val(price.qty).select();
        wprice.find('.price_name').html(price.name).prop('title', price.description);
        wprice.find('.pit').html(li.format_currency(price.pit));
        wprice.find('.vat').html(li.format_currency(price.vat));
        wprice.find('.tep').html(li.format_currency(price.tep));
        wprice.attr('data-price-id', price.id);
        // TODO: ids
        // TODO: nums
        
        if ( add )
          wprice.appendTo(wgauge.find('.declinations tbody'));
      }); // each bunch of tickets
    }); // each gauge
  }); // each manifestation
  
  $('#li_transaction_'+type+' .item .total').select();
}

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

li.completeContent = function(data, type, replaceAll = true)
{
  if ( typeof data != 'object' )
  {
    alert("<?php echo __('An error occured. Please try again.') ?>");
    return;
  }
  
  var wglobal = $('#li_transaction_'+type+' .families:not(.sample)'); // first element, parent of all
  var currency = $('#li_transaction_'+type+' .currency').html(); // currency (€, £, $...)
  
  if ( replaceAll )
  {
    wglobal = $('#li_transaction_'+type+' .families.sample').clone(true)
      .removeClass('sample');
    $('#li_transaction_'+type+' .families:not(.sample)').remove();
    wglobal.find('.family:not(.total)').remove();
    wglobal.appendTo($('#li_transaction_'+type));
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
      wmanif.attr('data-manifestation-id', manifestation.id);
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
        wprice.find('.pit').html(price.pit.toFixed(2)+' '+currency);
        wprice.find('.vat').html(price.vat.toFixed(2)+' '+currency);
        wprice.find('.tep').html(price.tep.toFixed(2)+' '+currency);
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

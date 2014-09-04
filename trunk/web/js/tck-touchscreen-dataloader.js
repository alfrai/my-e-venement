LI.completeContentTriggers = [];
LI.completeContent = function(data, type, replaceAll = true)
{
  console.log('populating '+type);
  if ( typeof data != 'object' )
  {
    LI.alert('An error occured. Please try again.','error');
    return;
  }
  
  // PAYMENTS
  switch ( type ) {
  case 'payments':
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
  
  
  // MANIFESTATIONS && PRODUCTS
  case 'store':
  case 'manifestations':
    var wglobal = $('#li_transaction_'+type+' .families:not(.sample)'); // first element, parent of all
    
    if ( replaceAll )
    {
      wglobal = $('#li_transaction_'+type+' .families.sample').clone(true)
        .removeClass('sample');
      $('#li_transaction_'+type+' .families:not(.sample)').remove();
      wglobal.find('.family:not(.total)').remove();
      wglobal.insertBefore($('#li_transaction_'+type+' .footer'));
    }
    
    // manifestations / products
    $.each(data, function(id, pdt){
      
      var wpdt = $('#li_transaction_'+type+' .families.sample .family:not(.total)').clone(true);
      var add = true;
      if ( $('#'+wpdt.prop('id')+pdt.id).length > 0 )
      {
        wpdt = $('#'+wpdt.prop('id')+pdt.id);
        add = false;
      }
      else
      {
        wpdt.prop('id', wpdt.prop('id')+pdt.id);
        wpdt.attr('data-family-id', pdt.id);
        wpdt.find('.item:not(.total)').remove();
      }
      
      // in progress: pdt
      wpdt.find('h3 .event').text(pdt.category).prop('href',pdt.category_url);
      wpdt.find('h3').css('background-color', pdt.color);
      // TODO (or not): declination_url
      
      // dates
      if ( pdt.happens_at )
      {
        var happens_at = new Date(pdt.happens_at.replace(' ','T'));
        var ends_at = pdt.ends_at ? new Date(pdt.ends_at.replace(' ','T')) : undefined;
        wpdt.find('h3 .happens_at').text(happens_at.toLocaleString().replace(/:\d\d \w+$/,'')).prop('href',pdt.product_url).prop('title', ends_at ? ends_at.toLocaleString().replace(/:\d\d \w+$/,'') : '');
      }
      else
        wpdt.find('h3 .happens_at').text(pdt.name).prop('href',pdt.product_url);
      
      // location
      if ( pdt.location )
        wpdt.find('h3 .location').text(pdt.location).prop('href',pdt.location_url);
      else
        wpdt.find('h3 .location').remove();
      
      if ( add )
        wpdt.insertBefore(wglobal.find('.family.total'));
      
      // gauges / declinations
      $.each(pdt[pdt.declinations_name], function(index, declination){
        var wdeclination = $('#li_transaction_'+type+' .families.sample .item:not(.total)').clone(true);
        var add = true;
        if ( $('#'+wdeclination.prop('id')+declination.id).length > 0 )
        {
          wdeclination = $('#'+wdeclination.prop('id')+declination.id);
          add = false;
        }
        else
        {
          wdeclination.find('.declination').remove();
          wdeclination
            .attr('data-'+declination.type+'-id', declination.id)
            .attr('data-type', declination.type)
            .prop('id', wdeclination.prop('id')+declination.id)
          ;
        }
        
        wdeclination.find('h4').text(declination.name);
        
        // prices
        wdeclination.find('.data .available_prices').remove();
        $('<span></span>').addClass('available_prices').html(JSON.stringify(declination.available_prices))
          .appendTo(wdeclination.find('.data'));
        
        // graphical gauges
        if ( type == 'manifestations' )
        {
          wdeclination.find('.data .gauge.raw').remove();
          $('<a></a>')
            .prop('href', declination.url)
            .addClass('gauge').addClass('raw')
            .appendTo(wdeclination.find('.data'));
          if ( declination.seated_plan_url && declination.seated_plan_seats_url )
          {
            wdeclination.find('.data .gauge.seated:not(.picture)').remove();
            $('<a></a>').addClass('gauge').addClass('seated')
              .prop('href', declination.seated_plan_seats_url)
              .append($('<img/>').prop('src', declination.seated_plan_url).prop('alt', 'seated-plan'))
              .appendTo(wdeclination.find('.data'));
          }
        }
        
        if ( add )
          wdeclination.insertBefore(wpdt.find('.item.total'));
        
        // in progress: prices
        if ( declination['prices'] != undefined )
        $.each(declination['prices'], function(index, price){
          if ( price.qty == 0 )
          {
            if ( !price.id )
              $('#li_transaction_'+type+' [data-'+declination.type+'-id='+declination.id+'] .declination.wip').remove();
            return;
          }
          var wprice = $('#li_transaction_'+type+' .families.sample .declination').clone(true);
          var add = true;
          if ( (tmp = wdeclination.find(str = '[data-price-id='+price.id+'].declination'+(price.state ? '.active.'+price.state : ':not(.active)'))).length > 0 )
          {
            wprice = tmp;
            add = false;
            wprice.find('.qty input').val(price.qty).select();
          }
          
          // check if price is available for this user
          var mod = false;
          $.each(declination.available_prices, function(k, p){
            if ( p.id === price.id )
              mod = true;
          });
          if ( !mod || price.state )
          {
            if ( parseInt(price.id)+'' === ''+price.id ) // everything but a Work In Progress price
              wprice.addClass('active');
            console.log(price.state);
            wprice.addClass(price.state ? price.state : 'readonly');
            if ( price.state === 'printed' || parseInt(price.id)+'' !== ''+price.id ) // every printed or Work In progress price
              wprice.find('.qty input').prop('readonly', true);
          }
          wprice.find('.qty input').val(price.qty).select();
          wprice.find('.price_name').html(price.name).prop('title', price.description);
          wprice.find('.pit').html(LI.format_currency(price.pit));
          wprice.find('.vat').html(price.vat ? LI.format_currency(price.vat) : '-');
          wprice.find('.tep').html(LI.format_currency(price.tep));
          wprice.find('.extra-taxes').html(price['extra-taxes'] ? LI.format_currency(price['extra-taxes']) : '-');
          if ( price['item-details'] )
            wprice.find('.item-details a').prop('href', wprice.find('.item-details a').prop('href')+'?price_id='+price.id+'&'+declination.type+'_id='+declination.id);
          else
            wprice.find('.item-details a').remove();
          wprice.attr('data-price-id', price.id);
          if ( parseInt(price.id,10)+'' !== ''+price.id )
            wprice.addClass('wip');
          
          // ids & numerotation
          var ids = [];
          $.each(price.ids, function(index, value){
            ids.push(value+( type == 'manifestations' && price.numerotation[index] ? ' '+price.numerotation[index] : '' ));
          });
          wprice.find('.ids').html('#'+ids.join(', #'));
          
          if ( add )
            wprice.appendTo(wdeclination.find('.declinations tbody'));
        }); // each bunch of tickets
      }); // each declination
    }); // each pdt
    
    $('#li_transaction_'+type+' .item .total').select();
    
    if ( typeof LI.completeContentTriggers == 'object' )
    {
      $.each(LI.completeContentTriggers, function(id, fct){
        fct(type, data);
      });
    }
    
    return true;
  
  default:
    console.log(type+' not implemented');
    return true;
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

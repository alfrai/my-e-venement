$(document).ready(function(){
  // sales ledger "less details" display (merge the lines for identical prices)
  $('#sales-ledger .ui-widget-header button').click(function(){
    $(this).fadeOut();
    $('#sales-ledger .see-more a').click();
    $('#sales-ledger').addClass('less-details');
    
    // build a data-layer that can be processed
    var arr = {}
    $('#sales-ledger .prices').each(function(){
      if ( arr[$(this).find('[data-manifestation-id]').attr('data-manifestation-id')] == undefined )
        arr[$(this).find('[data-manifestation-id]').attr('data-manifestation-id')] = [];
      if ( arr[$(this).find('[data-manifestation-id]').attr('data-manifestation-id')][$(this).find('[data-price-id]').attr('data-price-id')] == undefined )
        arr[$(this).find('[data-manifestation-id]').attr('data-manifestation-id')][$(this).find('[data-price-id]').attr('data-price-id')] = [];
      arr[$(this).find('[data-manifestation-id]').attr('data-manifestation-id')][$(this).find('[data-price-id]').attr('data-price-id')].push($(this));
    });
    
    console.error(arr);
    // graphical process
    $.each(arr, function(manif_id, manif){
    $.each(manif, function(price_id, elts){
      var currency = LI.get_currency(elts[0].find('.value').text());
      var fr_style = LI.currency_style(elts[0].find('.value').text());
      for ( var i = 1 ; i < elts.length ; i++ )
      {
        $.each(['.id-qty', '.value', '.extra-taxes'], function(j, selector){
          console.error(LI.clear_currency(elts[i].find(selector).text()));
          elts[0].find(selector).text(
            LI.clear_currency(elts[0].find(selector).text())
            +
            LI.clear_currency(elts[i].find(selector).text())
          );
          if ( selector != '.id-qty' )
            elts[0].find(selector).html(LI.format_currency(LI.clear_currency(elts[0].find(selector).text()), true, fr_style, currency));
        });
        elts[i].remove();
      }
      elts[0].find('.vat').text('');
    });
    });
    
    return false;
  });
  
  // cash ledger / method
  $('#ledger .method .see-more a').unbind().click(function(){
    $('#ledger .payment.method-'+parseInt(/#(\d+)$/.exec($(this).prop('href'))[1],10)).fadeToggle();
    $(this).html($(this).html() == '-' ? '+' : '-');
  }).click().html('+');
  
  // sales ledger / manifs
  $('#ledger-products .declination .see-more a, #ledger-events .manif .see-more a').unbind().click(function(){
    var line = /#(\w[\w-]+-\w+)$/.exec($(this).attr('href'))[1];
    if ( $(this).html() == '+' )
      $(this).closest('table').find('.'+line).fadeIn();
    else
      $(this).closest('table').find('.'+line).fadeOut();
    $(this).html( $(this).html() == '-' ? '+' : '-' );
  });
  
  // sales ledger / events
  var links = $('#ledger-products .product .see-more a, #ledger-events .event .see-more a').unbind().click(function(){
    var line = /#(\w+-\w+)$/.exec($(this).prop('href'))[1];
    if ( $(this).html() == '+' )
    {
      $(this).closest('table').find('.'+line).each(function(){
        $(this).fadeIn();
      });
    }
    else
    {
      $(this).closest('table').find('.'+line).each(function(){
        $(this).fadeOut();
        if ( $(this).find('.see-more a').html() == '-' )
          $(this).find('.see-more a').click();
      });
    }
    $(this).html($(this).html() == '-' ? '+' : '-');
  }).click();
  
  // hover on rows
  $('#ledger-events tbody tr, #ledger-products tbody tr, #ledger tbody tr').mouseenter(function(){
    $(this).addClass('ui-state-hover');
  });
  $('#ledger-events tbody tr, #ledger-products tbody tr, #ledger tbody tr').mouseleave(function(){
    $(this).removeClass('ui-state-hover');
  });
  
  // sorting datas...
  $('#cash-ledger thead .method').click(function(){
    $('#cash-ledger tbody tr.method').show();
    $('#cash-ledger tbody .payment').hide();
    $('#cash-ledger tbody .payment .method').text('');
    var methods = [];
    $('#cash-ledger tbody tr.method').each(function(){
      var arr = [];
      arr.push($(this));
      $(this).siblings('tr.payment.method-'+$(this).attr('data-id')).each(function(){
        arr.push($(this));
      });
      methods.push(arr);
    });
    for ( i = methods.length ; i > 0  ; i-- )
      $('#cash-ledger tbody').append(methods[i-1]);
  });
  
  $('#cash-ledger thead td:not(.method)').click(function(){
    if ( Cookie.get('tck_ledger_cash_order_column') != $(this).attr('class') )
    {
      Cookie.set('tck_ledger_cash_order_column', $(this).attr('class'), { maxAge: 30*24*60*60 }); // 30 days
      Cookie.set('tck_ledger_cash_order_type', 'asc', { maxAge: 30*24*60*60 }); // 30 days
    }
    else
      Cookie.set('tck_ledger_cash_order_type', Cookie.get('tck_ledger_cash_order_type') !== 'asc' ? 'asc' : 'desc', { maxAge: 30*24*60*60 }); // 30 days
    
    $('#cash-ledger tbody tr.method').hide();
    $('#cash-ledger tbody .payment').show();
    $('#cash-ledger tbody .payment').each(function(){
      $(this).find('.method').text($('#cash-ledger tbody .method[data-id='+$(this).attr('data-method-id')+']').find('.method').text());
    });
    var values = {};
    $('#cash-ledger tbody tr.payment').each(function(){
      var val = !isNaN(parseFloat($(this).find('.'+Cookie.get('tck_ledger_cash_order_column')).text().replace(',','.')) )
        ? parseFloat($(this).find('.'+Cookie.get('tck_ledger_cash_order_column')).text().replace(',','.'))
        : $(this).find('.'+Cookie.get('tck_ledger_cash_order_column')).text();
        
      if ( values[val] == undefined )
        values[val] = [];
      values[val].push($(this));
    });
    var order = Object.keys(values);
    if ( isNaN(order[0]) )
      Cookie.get('tck_ledger_cash_order_type') === 'asc' ? order.sort() : order.sort().reverse();
    else
      order.sort(function(a,b) { return Cookie.get('tck_ledger_cash_order_type') === 'asc' ? a - b : b - a; });
    $.each(order, function(i, id){
      for ( i = 0 ; i < values[id].length ; i++ )
        $('#cash-ledger tbody').append(values[id][i]);
    });
  });
  
  setTimeout(function(){
    if ( Cookie.get('tck_ledger_cash_order_column') )
      $('#cash-ledger thead .'+Cookie.get('tck_ledger_cash_order_column')).click();
  },2000);
});

  $(document).ready(function(){
    var currency = LI.get_currency($('.prices [data-price-id] .total:first').text());
    var fr_style = LI.currency_style($('.prices [data-price-id] .value:first').text()) == 'fr';
    if ( location.hash == '#debug' )
      console.error(currency+' '+$('.prices .value:first').text()+' '+fr_style);
  
    $('.prices .quantity select').change(function(){
      // hiding options to limit the global qty to the max value
      selects = $(this).closest('.gauge').find('.quantity select');
      var max_qty = 0;
      selects.each(function(){
        if ( parseInt($(this).find('option:last-child').val(),10) > max_qty )
          max_qty = parseInt($(this).find('option:last-child').val(),10);
      });
      for ( quantities = i = 0 ; i < selects.length ; i++ )
        quantities += parseInt($(selects[i]).val(),10);
      options = selects.find('option');
      options.show();
      for ( i = 0 ; i < options.length ; i++ )
      if ( parseInt($(options[i]).val(),10) > max_qty - quantities + parseInt($(options[i]).closest('select').val(),10) )
        $(options[i]).hide();
      
      // calculating totals by line
      var tmp = $(this).closest('tr').find('.value input').length > 0
        ? $(this).closest('tr').find('.value input').val()+''
        : $(this).closest('tr').find('.value').text();
      var val = LI.clear_currency(tmp) * parseInt($(this).val(),10);
      $(this).closest('tr').find('.total').html(LI.format_currency(val, true, fr_style, currency));
      
      // calculating the global total
      var value = 0;
      var taxes = 0;
      $(this).closest('tbody').find('[data-price-id]').each(function(){
        if ( !isNaN(LI.clear_currency($(this).find('.total').text())) )
          value += LI.clear_currency($(this).find('.total').text());
        if ( !isNaN(LI.clear_currency($(this).find('.extra-taxes').text())) )
          taxes += LI.clear_currency($(this).find('.extra-taxes').text());
      });
      $(this).closest('.prices').find('tfoot .total').html(LI.format_currency(value, true, fr_style, currency))
      $(this).closest('.prices').find('tfoot .extra-taxes').html(taxes > 0 ? LI.format_currency(taxes, true, fr_style, currency) : '')
    }).change();
  });

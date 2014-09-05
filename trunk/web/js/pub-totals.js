  $(document).ready(function(){
    $('.prices .quantity select').change(function(){
      // the currency
      currency = $(this).closest('tr').find('.value').text().replace(/^.*&nbsp;(.*)$/,'$1');
      
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
      var val = parseFloat($(this).closest('tr').find('.value').text().replace(',','.')) * parseInt($(this).val(),10);
      $(this).closest('tr').find('.total').html(LI.format_currency(val, currency));
      
      // calculating the global total
      var value = 0;
      var taxes = 0;
      $(this).closest('tbody').find('[data-price-id]').each(function(){
        if ( !isNaN(parseFloat($(this).find('.total').text().replace(',','.'))) )
          value += parseFloat($(this).find('.total').text().replace(',','.'));
        if ( !isNaN(parseFloat($(this).find('.extra-taxes').text().replace(',','.'))) )
          taxes += parseFloat($(this).find('.extra-taxes').text().replace(',','.'));
      });
      $(this).closest('.prices').find('tfoot .total').html(LI.format_currency(value,currency))
      $(this).closest('.prices').find('tfoot .extra-taxes').html(taxes > 0 ? LI.format_currency(taxes,currency) : '')
    }).change();
  });

  $(document).ready(function(){
    // concatenation of tickets which has the same price
    while ( $('#command tbody .tickets > :not(.done)').length > 0 )
    {
      ticket = $('#command tbody .tickets > :not(.done):first');
      price_id = ticket.attr('data-price-id');
      gauge_id = ticket.closest('tr').attr('id');
      ticket.closest('tr').find('.qty').html($('#command tbody #'+gauge_id+' .tickets > [data-price-id='+price_id+']').length);
      var value = 0;
      var taxes = 0;
      $('#command tbody #'+gauge_id+' .tickets > [data-price-id='+price_id+']').each(function(){
        value += parseFloat($(this).closest('tr').find('.value').html().replace(',','.'));
        var tmp = parseFloat($(this).closest('tr').find('.extra-taxes').html().replace(',','.'));
        if ( !isNaN(tmp) )
          taxes += tmp;
      });
      var currency = $.trim(ticket.closest('tr').find('.value').html()).replace(',','.').replace(/^\d+\.{0,1}\d*&nbsp;(.*)$/,'$1');
      
      ticket.closest('tr').find('.total').html(value.toFixed(2)+currency);
      ticket.closest('tr').find('.extra-taxes').html(taxes.toFixed(2)+currency);
      ticket.addClass('done');
      $('#command tbody #'+gauge_id+' .tickets > [data-price-id='+price_id+']:not(.done)').remove();
    }
    
    // removing empty lines
    $('#command tbody tr').each(function(){
      if ( $(this).find('.tickets .done').length == 0 )
        $(this).remove();
    });
  });

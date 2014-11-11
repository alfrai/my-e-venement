  $(document).ready(function(){
    // removing the recommandations if none
    if ( $('#cmd-links .products .link, #cmd-links .manifestations .link').length == 0 )
      $('#cmd-links').hide();
    
    // removing options if none
    if ( $('#command tbody .linked-stuff li').length == 0 )
      $('#command thead .linked-stuff').css('font-size', 0);
    
    // adding a button on named tickets
    setTimeout(function(){
      $('<button>ok</button>').insertAfter($('#command .named-tickets .contact .contact_firstname')).click(function(){ return false });
    },2500);
    
    // event's picture
    $('#command tbody tr.tickets').addClass('picture-to-merge');
    var trs;
    for ( i = 0 ; (trs = $('#command tbody tr.picture-to-merge')).length > 0 && i < 200 ; i++ ) // var i is a protection
    {
      var tr = trs.first();
      tr.find('td.picture').prop('rowspan', trs.parent().find('[data-manifestation-id='+tr.attr('data-manifestation-id')+']').length);
      tr.parent().find('[data-manifestation-id='+tr.attr('data-manifestation-id')+']').removeClass('picture-to-merge').not(tr).find('td:first').remove();
    }
      
    // stop here if needed
    if ( $('#command thead .qty').length == 0 )
      return;
    
    // concatenation of tickets which has the same price
    while ( $('#command tbody > :not(.products) .tickets > :not(.done)').length > 0 )
    {
      ticket = $('#command tbody .tickets > :not(.done):first');
      var data_id;
      price_id = ticket.attr('data-price-id') ? ticket.attr(data_id = 'data-price-id') : ticket.attr(data_id = 'data-mct-id');
      gauge_id = ticket.closest('tr').attr('id');
      ticket.closest('tr').find('.qty').html($('#command tbody #'+gauge_id+' .tickets > ['+data_id+'='+price_id+']').length);
      var value = 0;
      var taxes = 0;
      $('#command tbody #'+gauge_id+' .tickets > ['+data_id+'='+price_id+']').each(function(){
        value += parseFloat($(this).closest('tr').find('.value').html().replace(',','.'));
        var tmp = parseFloat($(this).closest('tr').find('.extra-taxes').html().replace(',','.'));
        if ( !isNaN(tmp) )
          taxes += tmp;
      });
      var currency = $.trim(ticket.closest('tr').find('.value').html()).replace(',','.').replace(/^\d+\.{0,1}\d*&nbsp;(.*)$/,'$1');
      
      ticket.closest('tr').find('.total').html(LI.format_currency(value, currency));
      ticket.closest('tr').find('.extra-taxes').html(LI.format_currency(taxes, currency));
      ticket.addClass('done');
      $('#command tbody #'+gauge_id+' .tickets > ['+data_id+'='+price_id+']:not(.done)').remove();
    }
    
    // products
    $('#command tbody > .products').addClass('todo');
    while ( $('#command tbody > .products.todo').length > 0 )
    {
      var pdt = $('#command tbody > .products.todo:first');
      var currency = $.trim($(pdt).find('.value').text()).replace(',','.').replace(/^\d+\.{0,1}\d*&nbsp;(.*)$/,'$1');
      
      // compare to other lines "todo"
      $('#command tbody > .products.todo:not(:first)').each(function(){
        var go = true;
        var compare = this;
        $.each(['.event', '.manifestation', '.workspace', '.tickets'], function(i, field){
          if ( $(compare).find(field).text() != $(pdt).find(field).text() )
            go = false;
        });
        if ( go )
        {
          if ( $(pdt).find('.value').text() != $(this).find('.value').text() )
            $(pdt).find('.value').text('-');
          
          $(pdt).find('.total').html(LI.format_currency(parseFloat($(pdt).find('.total').text()) + parseFloat($(this).find('.value').text()), currency));
          $(pdt).find('.qty').text(parseInt($(pdt).find('.qty').text(),10) + parseInt($(this).find('.qty').text(),10));
          $(this).remove();
        }
      });
      
      $(pdt).removeClass('todo');
    }
    
    // removing empty lines
    $('#command tbody > :not(.products)').each(function(){
      if ( $(this).find('.tickets .done').length == 0 )
        $(this).remove();
    });
  });

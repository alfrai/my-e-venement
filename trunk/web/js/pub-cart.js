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
    LI.pubCartPictureRowspan();
      
    // stop here if needed
    if ( $('#command thead .qty').length == 0 )
      return;
    
    // if continuing, removing the rowspan on pictures
    $('#command td.picture').prop('rowspan', null);
    
    // concatenation of tickets that have the same price
    while ( $('#command tbody > :not(.products):not(.done)').length > 0 )
    {
      var data_id;
      var ticket = $('#command tbody > :not(.products):not(.done):first');
      
      price_id = ticket.find('.tickets > [data-price-id]').length > 0 ? ticket.find('.tickets > [data-price-id]').attr(data_id = 'data-price-id') : ticket.find('.tickets > [data-mct-id]').attr(data_id = 'data-mct-id');
      gauge_id = ticket.attr('data-gauge-id');
      ticket.find('.qty').text($('#command tbody [data-gauge-id='+gauge_id+'] .tickets > ['+data_id+'='+price_id+']').length);
      var value = 0;
      var taxes = 0;
      $('#command tbody [data-gauge-id='+gauge_id+'] .tickets > ['+data_id+'='+price_id+']').each(function(){
        value += parseFloat($(this).closest('tr').find('.value').html().replace(',','.'));
        var tmp = parseFloat($(this).closest('tr').find('.extra-taxes').html().replace(',','.'));
        if ( !isNaN(tmp) )
          taxes += tmp;
      });
      var currency = $.trim(ticket.find('.value').text()).replace(',','.').replace(/^\d+\.{0,1}\d*&nbsp;(.*)$/,'$1');
      
      ticket.find('.total').html(LI.format_currency(value, currency));
      ticket.find('.extra-taxes').html(LI.format_currency(taxes, currency));
      ticket.addClass('done');
      $('#command tbody > [data-gauge-id='+gauge_id+']:not(.done) .tickets > ['+data_id+'='+price_id+']').closest('[data-gauge-id]').remove();
    }
    
    // event's picture
    LI.pubCartPictureRowspan();
    
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
  });

if ( LI == undefined )
  var LI = {};
LI.pubCartPictureRowspan = function()
{
  $('#command tbody tr.tickets').addClass('picture-to-merge');
  var trs;
  for ( i = 0 ; (trs = $('#command tbody tr.picture-to-merge')).length > 0 && i < 200 ; i++ ) // var i is a protection
  {
    var tr = trs.first();
    tr.find('td.picture').prop('rowspan', trs.parent().find('[data-manifestation-id='+tr.attr('data-manifestation-id')+']').length);
    tr.parent().find('[data-manifestation-id='+tr.attr('data-manifestation-id')+']').removeClass('picture-to-merge').not(tr).find('td:first').hide();
  }
}

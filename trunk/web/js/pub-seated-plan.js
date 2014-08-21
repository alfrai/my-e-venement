// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};
        
$(document).ready(function(){
  $('.prices [data-price-id] .quantity select').each(function(){
    var val;
    $(this)
      .focusin(function(){ val = $(this).val(); })
      .focusout(function(){
        if ( val !== $(this).val() )
        {
          var seats = [];
          
          // adding tickets
          for ( i = 0 ; i < $(this).val() - val ; i++ )
          if ( $(this).closest('.gauge').find('.prices .seating.in-progress .seat').eq(i).length > 0 )
          {
            // in the list
            var seat = $(this).closest('.gauge').find('.prices .seating.in-progress .seat').eq(i);
            var hidden = $('<input type="hidden" />')
              .prop('name',
                'price['+
                $(this).closest('[data-gauge-id]').attr('data-gauge-id')+
                ']['+
                $(this).closest('[data-price-id]').attr('data-price-id')+
                '][seat_id][]'
              )
              .val(seat.attr('data-seat-id'));
            
            seat.append(hidden)
                .prependTo($(this).closest('[data-price-id]').find('.seats'));
            
            // preparing the DB
            seats.push({
              seat_id: hidden.val(),
              gauge_id: $(this).closest('[data-gauge-id]').attr('data-gauge-id'),
              price_id: $(this).closest('[data-price-id]').attr('data-price-id')
            });
          }
          
          // removing tickets
          for ( i = 0 ; i < $(this).closest('[data-price-id]').find('.seat').length - $(this).val() ; i++ )
          if ( $(this).closest('[data-price-id]').find('.seat').eq(i).length > 0 )
          {
            // preparing the DB
            seats.push({
              seat_id: $(this).closest('[data-price-id]').find('.seat').eq(i).attr('data-seat-id'),
              gauge_id: $(this).closest('[data-gauge-id]').attr('data-gauge-id'),
            });
            
            // in the list
            $(this).closest('[data-price-id]').find('.seat').eq(i)
              .appendTo($(this).closest('.gauge').find('.prices .seating.in-progress .seats'))
              .find('input').remove();
          }
          
          // the quantity of WIPs
          $(this).closest('.gauge').find('.prices .seating.in-progress .quantity').text(
            $(this).closest('.gauge').find('.prices .seating.in-progress .seat').length
          );
          
          // in the DB
          if ( seats.length > 0 )
          $.get($('#mod-seated-tickets').prop('href'), { seats: seats }, function(json){
            LI.alert(json.error ? json.error.message : json.success.message, json.error ? 'error' : 'success');
          });
        }
      })
    ;
  });
  
  LI.seatedPlanInitializationFunctions.push(function(){
    LI.pubAfterSeating(); // refreshing the data in the prices list
    
    $('.seated-plan .seat.txt').unbind('contextmenu').click(function(){
      var seat = this;
      if ( $(seat).is('.in-progress') )   // removing a seat
      {
        $.post($(seat).closest('.full-seating').find('.remove-ticket').prop('href'), { seat_id: $(seat).attr('data-id') }, function(json){
          if ( json.error )
          {
            if ( json.error.message )
              LI.alert(json.error.message, 'error');
          }
          else
          {
            // on the plan
            $(seat).parent().find('.seat-'+$(seat).attr('data-id'))
              .removeClass('in-progress')
              .removeClass('ordered');
            
            // message
            if ( json.success.message )
              LI.alert(json.success.message, 'success');
          }
          LI.pubAfterSeating(seat);
        });
      }
      else  // adding a seat
      {
        $.post($(seat).closest('.full-seating').find('.add-seat').prop('href'), {
          seat_id: $(seat).attr('data-id')
        }, function(json){
          if ( json.error )
          {
            if ( json.error.message )
              LI.alert(json.error.message, 'error');
          }
          else
          {
            // on the plan
            $(seat).parent().find('.seat-'+$(seat).attr('data-id'))
              .addClass('in-progress')
              .addClass('ordered');
            
            // in the list, if first ticket added like that
            if ( $(seat).closest('.gauge').find('.prices tbody .seating.in-progress').length == 0 )
            {
              var tr = $(seat).closest('.gauge').find('.prices tbody tr[data-price-id]:first').clone(true);
              tr.removeAttr('data-price-id')
                .addClass('seating').addClass('in-progress')
                .prependTo($(seat).closest('.gauge').find('.prices tbody'));
              tr.find('.price').text(json.success.name);
              tr.find('.seats').text('');
            }
            tr = $(seat).closest('.gauge').find('.prices tbody .seating.in-progress');
            tr.find('.seats').append($('<span></span>').addClass('seat').text($(seat).attr('data-num')));
            
            // message
            if ( json.success.message )
              LI.alert(json.success.message, 'success');
          }
          LI.pubAfterSeating(seat);
        });
      }
    });
  });
});

// after adding/removing a ticket on a seat, this is what happens:
LI.pubAfterSeating = function(seat)
{
  // no seat is given, refresh everything
  if ( seat == undefined )
    seat = $('.gauge .seated-plan.picture .anti-handling');
  
  // in the list, changing the quantity of WIP and normal tickets
  $(seat).closest('.gauge').find('.prices tbody tr').each(function(){
    var tr = this;
    var gauge_id = $(tr).closest('.gauge').attr('data-gauge-id');
    var price_id = $(tr).attr('data-price-id');
    
    var selector = '.seat.txt.in-progress';
    selector += price_id ? '[data-price-id='+price_id+']' : ':not([data-price-id])';  // ? !WIP : WIP
    var seats = $(seat).closest('.seated-plan').find(selector);
    var nb = seats.length;
    
    // the seat's names
    $(tr).find('.seats *').remove();
    seats.each(function(){
      var input = !$(this).attr('data-price-id') ? $('') : $('<input type="hidden" />')
        .prop('name', 'price['+$(this).attr('data-gauge-id')+']['+$(this).attr('data-price-id')+'][seat_id][]')
        .val($(this).attr('data-id'));
      var label = $(this).attr('data-num');
      var elt = $('<span></span>')
        .addClass('seat')
        .attr('data-seat-id', $(this).attr('data-id'));
      $(tr).find('.seats').append(elt.append(input).append(label));
    });
    
    if ( !price_id ) // WIP
    {
      $(this).find('.value, .quantity, .total').text('-');
      if ( nb > 0 )
        $(this).find('.quantity').text(nb);
      else
        $(this).remove();
    }
    else // normal tickets
      $(this).find('.quantity select').val(nb);
  });
}

LI.pubAutoSeating = function(elt){
  $.post($(elt).val(), $(elt).closest('form').serialize(), function(json){
    if ( json.success )
    {
      LI.alert(json.success.message, 'success');
      $('.gauge .prices tbody .seating.in-progress').remove();
      $('.gauge .prices tbody .seats > *').remove();
      
      $.each(json.success.seats, function(gid, prices){
        $.each(prices, function(pid, seats){
          var td = $('.gauge[data-gauge-id='+gid+'] .prices [data-price-id='+pid+'] .seats');
          $.each(seats, function(sid, seat){
            var span  = $('<span></span>').attr('data-seat-id', sid).addClass('seat').text(seat);
            var input = $('<input type="hidden" />')
              .prop('name', 'price['+$(elt).closest('.gauge').attr('data-gauge-id')+']['+pid+'][seat_id][]')
              .val(sid);
            span.append(input).appendTo(td);
          });
        });
      });
    }
    
    if ( json.error )
      LI.alert(json.error.message, 'error');
    
    // refresh the seated plan
    $(elt).closest('.gauge').find('.load-data').click();
  });
}

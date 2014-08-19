// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};
        
LI.pubAutoSeating = function(elt){
  $.get($(elt).val(), $(elt).closest('form').serialize(), function(json){
    if ( json.success )
    {
      LI.alert(json.success.message, 'success');
      $('.gauge .prices tbody .seats > *').remove();
      
      $.each(json.success.seats, function(gid, prices){
        $.each(prices, function(pid, seats){
          var td = $('.gauge[data-gauge-id='+gid+'] .prices [data-price-id='+pid+'] .seats');
          var html = '';
          $.each(seats, function(sid, seat){
            html += '<span data-seat-id="'+sid+'" class="seat">' + seat + '<input type="hidden" name="price['+$(elt).closest('.gauge').attr('data-gauge-id')+']['+pid+'][seat_id][]" value="'+sid+'" /></span> ';
          });
          td.html(html);
        });
      });
      
      // refresh the seated plan
      $(elt).closest('.gauge').find('.load-data').click();
    }
    if ( json.error )
      LI.alert(json.error.message, 'error');
  });
}    

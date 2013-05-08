function gauge_small()
{
  $('.sf_admin_list_td_list_manifestations_gauges').addClass('small-gauges'); // a trick for CSS to permit classical rendering compatibility
  $('.sf_admin_list_td_list_manifestations_gauges br').remove();
  
  $('.sf_admin_list_td_list_manifestations_gauges .gauge').each(function(){
    $(this).find('> *').each(function(){
      // every children except for total
      if ( $(this).hasClass('total') )
        return true;
      
      // ... and except booked which is useless graphically
      if ( $(this).hasClass('booked') )
        return true;
      
      // get back local data
      count = parseInt($(this).html());
      total = parseInt($(this).closest('.gauge').find('.total').html());
      
      // set properties
      $(this)
        .prop('title',count+' '+$(this).prop('title')+' / '+total)
        .css('width',(count/total*100)+'px');
    });
    
    $(this).prop('title', (total=parseInt($(this).find('.total').html())) - (booked=parseInt($(this).find('.booked').html()))+' / '+total);
    if ( booked > total )
      $(this).addClass('overbooked');
  });
}

$(document).ready(function(){
  gauge_small();
  
  // for hypothetical pagination...
  if ( window.list_scroll_end == undefined )
  {
    window.list_scroll_end = new Array()
    window.list_scroll_end[0] = gauge_small;
  }
  else
    window.list_scroll_end[window.list_scroll_end.length] = gauge_small;
});

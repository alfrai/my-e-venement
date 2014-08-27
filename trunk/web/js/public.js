// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

if ( LI.pubCartReady == undefined )
  LI.pubCartReady = [];

$(document).ready(function(){
  $.get($('#cart-widget-url').prop('href'),function(data){
    $('body').prepend($($.parseHTML(data)).find('#cart-widget'));
    
    for ( i = 0 ; LI.pubCartReady[i] != undefined ; i++ )
      LI.pubCartReady[i]();
  });
  
  // if treating month as a structural data
  if ( $('.sf_admin_list .sf_admin_list_th_month').css('display') != 'none' )
  {
    // removing the ordering feature from the table's header
    $('.sf_admin_list th a').each(function(){
      $(this).closest('th').html($(this).html());
    });
    
    // dividing events by their manifestations' month (so there is a duplication of events if 2 manifs happen in 2 different month)
    var arr = [];
    $('.sf_admin_list tbody .sf_admin_list_td_month').each(function(){
      var evt = $(this).closest('.sf_admin_row');
      
      $(this).find('.month:not(:first)').each(function(){
        var nevt = evt.clone().insertAfter(evt);
        var month = evt.find('.month:last').clone().removeClass('month').prop('class');
        
        evt.find('.month:last').remove();
        nevt.find('.month:not(:last)').remove();
        nevt.find('.sf_admin_list_td_dates li:not(.'+month+')').remove();
        
        if ( arr.indexOf(month) == -1 )
          arr.push(month);
      });
      
      var month = '.'+evt.find('.month:first').clone().removeClass('month').prop('class');
      evt.find('.sf_admin_list_td_dates li:not('+month+')').remove();
    });
    
    // adding a class depending on current month on every event
    $('.sf_admin_list tbody .sf_admin_row').each(function(){
      var month = $(this).find('.sf_admin_list_td_month .month').clone().removeClass('month').prop('class');
      $(this).addClass(month);
    });
    
    // reordering globally using the event's month (class added recently)
    $.each(arr, function(i, month){
      var first = $('.sf_admin_list tbody .sf_admin_row.'+month+':first');
      $('.sf_admin_list tbody .sf_admin_row.'+month+':not(:first)').each(function(){
        $(this).insertAfter(first);
      });
    });
    
    // reordering inside the month groups, by the date of the first manifestation
    $('.sf_admin_list tbody .sf_admin_row .sf_admin_list_td_dates li:first-child').each(function(){
      var cur = parseInt($(this).attr('data-time'));
      var next = parseInt($(this).closest('.sf_admin_row').next().find('.sf_admin_list_td_dates li:first').attr('data-time'));
      if ( cur > next )
        $(this).closest('.sf_admin_row').next().insertBefore($(this).closest('.sf_admin_row'));
    });
    
    // grouping by month
    var month = '';
    var colspan = $('.sf_admin_list thead tr:first th').length;
    $('.sf_admin_list tbody .sf_admin_list_td_month').each(function(){
      if ( month != $(this).find('.month:first').html() )
      {
        month = $(this).find('.month:first').html();
        $('<tr></tr>').addClass('sf_admin_month').insertBefore($(this).closest('tr'))
          .append($('<td></td>').html(month).prop('colspan', colspan));
      }
      $(this).html('');
    });
  }
});
        

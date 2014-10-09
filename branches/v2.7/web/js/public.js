// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  $.get($('#cart-widget-url').prop('href'),function(data){
    $('body').prepend($($.parseHTML(data)).find('#cart-widget'));
  });
  
  // if treating month as a structural data
  if ( $('.sf_admin_list .sf_admin_list_th_month').length > 0
    && $('.sf_admin_list .sf_admin_list_th_month').css('display') != 'none' )
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
  
  // if treating day as a structural data (in the manifestations list)
  if ( $('.sf_admin_list .sf_admin_list_th_happens_at_time_h_r').length > 0
    && $('.sf_admin_list .sf_admin_list_th_happens_at_time_h_r').css('display') != 'none' )
  {
    // dividing manifestations by their day
    var arr = {};
    $('.sf_admin_list tbody .sf_admin_list_td_list_happens_at').each(function(){
      var evt = $(this).closest('.sf_admin_row');
      var date = new Date($.trim($(this).text()).replace(' ', 'T'));
      var tmp = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
      
      if ( arr[tmp] == undefined )
        arr[tmp] = [];
      arr[tmp].push(evt);
    });
    
    var colspan = $('.sf_admin_list tbody tr:first td').length;
    var dates = Object.keys(arr);
    dates.sort(function(a,b){return b-a;});
    $.each(dates, function(i, key){
      date = new Date(key);
      var td = $('<td colspan="'+colspan+'"></td>').text(
        $.trim(arr[key][0].find('.sf_admin_list_td_list_day_name').text())
        +' '+
        date.getDate()+'/'+(date.getMonth()+1)
      );
      var tr = $('<tr></tr>')
        .addClass('sort-by-day')
        .append(td)
        .prependTo($('.sf_admin_list tbody'))
        .after(arr[key]);
    });
  }
  
  // change quantities in manifestations list
  $('.sf_admin_list_td_list_tickets .qty input').change(function(){
    $(this).closest('form').submit();
    LI.manifCalculateTotal(this);
  });
  LI.manifCalculateTotal();
  $('.sf_admin_list_td_list_tickets form').submit(function(){
    $.ajax({
      type: $(this).prop('method'),
      url: $(this).prop('action'),
      data: $(this).serialize(),
      success: function(json){
        console.error(json.toSource());
        if ( json.message )
          LI.alert(json.message, 'error');
        
        if ( !json.tickets || json.tickets.length == 0 )
        {
          $('.sf_admin_list_td_list_tickets form .qty input').val(0);
          return;
        }
        
        $.each(json.tickets, function(gauge_id, price){
          $.each(price, function(price_id, qty){
            console.error(gauge_id+' '+price_id+' '+qty);
            $(str = '.sf_admin_list_td_list_tickets [data-gauge-id='+gauge_id+'] [data-price-id='+price_id+'] .qty input').val(qty);
            console.error(str);
          });
        });
      }
    });
    return false;
  });
});

LI.manifCalculateTotal = function(elt){
  if ( elt == undefined )
    elt = $('.sf_admin_list_td_list_tickets .qty input');
  $(elt).each(function(){
    $(this).closest('form').find('.total').html(
      LI.format_currency(parseInt($(this).val(),10) * parseFloat($(this).closest('form').find('.value').text()))
    );
  });
}

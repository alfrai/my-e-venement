$(document).ready(function(){
  // recording a chart as a CSV file
  $('.actions a.record').mouseenter(function(){
    $(this).parent().find('.arrow').addClass('pos1');
  });
  $('.actions a.record').mouseout(function(){
    $(this).parent().find('.arrow').removeClass('pos1');
  });
  
  // filtering
  $('#sf_admin_filter .ui-dialog-titlebar-close').click(function(){
    $('#sf_admin_filter').hide();
    $('.chart embed, .chart iframe').css('visibility',$('.chart embed').css('visibility') == 'hidden' ? '' : 'hidden');
  });
  $('#sf_admin_filter_button').click(function(){
    $('#sf_admin_filter').toggle();
    $('.chart embed, .chart iframe').css('visibility',$('.chart embed').css('visibility') == 'hidden' ? '' : 'hidden');
    return false;
  });
  $('#sf_admin_filter button').click(function(){
    $('#criterias').submit();
  });
});

$(document).ready(function(){
  // hover on rows
  $('.summary tbody tr').mouseenter(function(){
    $(this).addClass('ui-state-hover');
  });
  $('.summary tbody tr').mouseleave(function(){
    $(this).removeClass('ui-state-hover');
  });
  
  // specific paging
  action = /\/summary\/(.*)\/action/.exec(window.location)[1];
  $('#sf_admin_pager a').each(function(){
    href = $(this).attr('href').replace(/\/summary\?/,'/summary/'+action+'/action?');
    $(this).attr('href',href);
  });
  $('#sf_admin_pager input[onkeypress]').removeAttr('onkeypress')
    .keypress(function(event){
      if ( event.keyCode == 13 )
      {
        uri = /^(.*)\?.*$/.exec(window.location)[1];
        window.location = uri+'?page='+$(this).val();
        $(this).closest('form').submit(function(){ return false; });
        return false;
      }
    });
  
  // specific filtering
  action = /\/summary\/(.*)\/action/.exec(window.location)[1];
  $('#sf_admin_filter form').each(function(){
    $(this).attr('action',$(this).attr('action')+'?type='+action);
  });
  $('#sf_admin_filters_buttons a').each(function(){
    $(this).attr('href',$(this).attr('href')+'&type='+action);
  });
});

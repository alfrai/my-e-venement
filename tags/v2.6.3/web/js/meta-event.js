//$(document).ready(function(){ events_list(); });

function events_list(url)
{
  if ( url != undefined ) events_url = url;
    
  $('.events_list').load(events_url+' .sf_admin_list',function(){
    $('.events_list caption, .events_list > * > table > tbody > tr > td:first-child, '+
      '.events_list #sf_admin_list_batch_actions, '+
      '.events_list .sf_admin_list_th_event_category_description, '+
      '.events_list .sf_admin_list_td_event_category_description, '+
      '.events_list thead .ui-icon, '+
      '.events_list .sf_admin_action_edit, .events_list .sf_admin_action_delete')
      .remove();
    $('.events_list thead a').removeAttr('href');
    
    $('.events_list tfoot input').after($('.events_list tfoot input').val());
    $('.events_list tfoot input').remove();
    
    $('.events_list tfoot a[href]').click(function(){
      events_list($(this).prop('href'));
      return false;
    });
  });
}

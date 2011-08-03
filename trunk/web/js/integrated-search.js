function list_integrated_search(data)
{
  // replacing only necessary content
  filters = $('#sf_admin_filters_buttons').clone(true);
  $('.sf_admin_list > table').replaceWith($(data).find('.sf_admin_list > table'));
  $('#sf_admin_filters_buttons').replaceWith(filters);
  
  // updating links to order_by and pagination
  $('.sf_admin_list > table > thead a, .sf_admin_list > table > tfoot a').each(function(){
    $(this).attr('href',
      $(this).attr('href').replace(
        $('#list-integrated-search input[name=url]').val(),
        $('#list-integrated-search').attr('action')
      )+'&s='+$('#list-integrated-search input[name=s]').val()
    );
  }).click(function(){
    $.get($(this).attr('href'),function(data){
      list_integrated_search(data);
    });
    return false;
  });
  
  // disabling the input which permits to change current page arbitrary
  $('.sf_admin_list > table > tfoot [name=page]')
    .attr('disabled','disabled')
    .attr('style','background-color: white');
  
  // disabling the extra-actions
  if ( $('#sf_admin_actions_menu .sf_admin_action_group, #sf_admin_actions_menu .sf_admin_action_csv').length > 0 )
  {
    $('#sf_admin_actions_menu .sf_admin_action_group, #sf_admin_actions_menu .sf_admin_action_labels, #sf_admin_actions_menu .sf_admin_action_csv')
      .remove();
    $('#sf_admin_actions_button').unbind().menu({
      content: $('#sf_admin_actions_menu').html(),
      showSpeed: 300
    });
  }
}

$(document).ready(function(){
  $('#list-integrated-search').unbind().submit(function(){
    if ( $(this).find('input[name=s]').val() == '' )
    {
      window.location = $(this).find('input[name=contact_url]').val();
      return false;
    }
    
    $.get($(this).attr('action'),{ s: $(this).find('input[name=s]').val() },function(data){
      list_integrated_search(data);
    });
    return false;
  });
});

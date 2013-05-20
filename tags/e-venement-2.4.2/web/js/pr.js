$(document).ready(function(){
  $('.sf_admin_list .sf_admin_action_show a, .sf_admin_list .sf_admin_action_edit a').unbind().click(function(){
    $('#sf_admin_footer iframe').prop('src',$(this).prop('href')).css('height','600px');
    $('.sf_admin_list caption h1 a[href="#"]').click();
    window.location = '#top';
    return false;
  });
  
  // to load content in a new window
  $('#sf_fieldset_ticketting a, .show_groups a').prop('target','_blank');
  
  // to disable inputs if update is not available
  contact_disable_inputs();
});

function contact_disable_inputs()
{
  $(':not(#sf_admin_filter) > form:not(#list-integrated-search)').each(function(){
    if ( $(this).find('.submit').length == 0 )
      $(this).find('input[type=text], textarea, select').prop('disabled','disabled');
  });
}

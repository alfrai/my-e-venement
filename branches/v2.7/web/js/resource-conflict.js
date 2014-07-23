$(document).ready(function(){
  $('.sf_admin_list .sf_admin_row').each(function(){
    if ( $.trim($(this).find('.sf_admin_list_td_list_resource').html()) != '' )
      $(this).find('.sf_admin_batch_checkbox, .sf_admin_action_confirm').remove();
  });
});

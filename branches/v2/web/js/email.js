$(document).ready(function(){

setTimeout(function() {
  if ( $('#sf_admin_content a[href="#'+$('#sf_admin_form_tab_menu .ui-state-error:first').parent().attr('id')+'"]').length > 0 )
    $('#sf_admin_content a[href="#'+$('#sf_admin_form_tab_menu .ui-state-error:first').parent().attr('id')+'"]').click();
  else if ( $('.sf_admin_form .sf_admin_form_is_new').length == 0 )
    $('#sf_admin_content a[href="#sf_fieldset_3__validate"]').click();
},1000);

$('#email-send-button').click(function(){
  return confirm($(this).find('.confirm-msg').html());
});

if ( $('.members .contacts').length > 0 )
  $.get(email_contacts_url, email_contacts_list);
if ( $('.members .organisms').length > 0 )
  $.get(email_organisms_url, email_organisms_list);

});

function email_contacts_list(data)
{
  $('.members .contacts').html($(data).find('.sf_admin_list'));
  $('.members .contacts tfoot a[href]').unbind().click(function(){
    $.get($(this).attr('href'), email_contacts_list);
    return false;
  });
}
function email_organisms_list(data)
{
  $('.members .organisms').html($(data).find('.sf_admin_list'));
  $('.members .organisms tfoot a[href]').unbind().click(function(){
    $.get($(this).attr('href'), email_organisms_list);
    return false;
  });
}

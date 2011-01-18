$(document).ready(function(){

setTimeout(function() {
  if ( $('#sf_admin_content a[href="#'+$('#sf_admin_form_tab_menu .ui-state-error:first').parent().attr('id')+'"]').length > 0 )
  {
    $('#sf_admin_content a[href="#'+$('#sf_admin_form_tab_menu .ui-state-error:first').parent().attr('id')+'"]').click();
  }
  else if ( $('.sf_admin_form .sf_admin_form_is_new').length == 0 )
  {
    $('#sf_admin_content a[href="#sf_fieldset_3__validate"]').click();
  }
},1000);

$('#email-send-button').click(function(){
  return confirm($(this).find('.confirm-msg').html());
});

});

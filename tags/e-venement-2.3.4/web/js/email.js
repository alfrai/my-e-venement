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

// attachments
if ( $('[name="email[id]"]').val() == '' )
{
  $('.sf_admin_form_field_attachments').hide();
  
  setTimeout(function(){
    tinyMCE.activeEditor.onChange.add(manage_attachment_widget);
  },1000);
  $('[name="email[field_subject]"]').change(manage_attachment_widget);
}
$('.attachment-new').click(function(){
  if ( $.trim($('[name="email[field_subject]"]').val()) == '' || $.trim(tinyMCE.activeEditor.getContent()) == '' )
    return false;
  
  // tinymce
  tinyMCE.activeEditor.save();
  
  if ( $('[name="email[id]"]').val() == '' )
  {
    $('.open_list_selected option').attr('selected',true); // not to loose all contacts & so
    $.post($('form').attr('action'),$('form').serialize(),function(data){
      window.location = $(data).find('.attachment-new a').attr('href');
    });
    return false;
  }

  $.post($('form').attr('action'),$('form').serialize());
});

});

function manage_attachment_widget(ed, l)
{
  if ( $.trim($('[name="email[field_subject]"]').val()) != ''
    && $.trim(tinyMCE.activeEditor.getContent()) != '' )
    $('.sf_admin_form_field_attachments').fadeIn();
  else
    $('.sf_admin_form_field_attachments').fadeOut();
}

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

function email_urlconvertor(url, node, on_save)
{
  alert(url);
  return url;
}

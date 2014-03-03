$(document).ready(function(){
  $('select[name="manifestation[event_id]"]').each(function(){
    if ( $(this).find('option[selected=selected]').length > 0 )
    if ( $(this).val() != '' )
    {
      $(this).prop('disabled','disabled');
      elt = $('<input type="hidden" name="'+$(this).prop('name')+'" value="'+$(this).find('option:selected').prop('value')+'" />');
      elt.insertAfter($(this));
      
      if ( $(this).val() )
      {
        form = $('.sf_admin_form form:first').parents().find('form');
        if ( $(this).prop('name') == 'manifestation[event_id]' )
          tmp = 'event';
        else
          tmp = 'location';
          
        args = form.prop('action',form.prop('action').indexOf('?') != -1
          ? form.prop('action')+'&'+tmp+'='+$(this).val()
          : form.prop('action')+'?'+tmp+'='+$(this).val());
      }
    }
  });
  
  $('#sf_fieldset_tickets .contact a').each(function(){
    $(this).prop('title',$(this).html());
  });
});

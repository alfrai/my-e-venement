$(document).ready(function(){
  $('select[name="manifestation[event_id]"], select[name="manifestation[location_id]"]').each(function(){
    if ( $(this).find('option[selected=selected]').length > 0 )
    if ( $(this).val() != '' )
    {
      $(this).attr('disabled','disabled');
      elt = $('<input type="hidden" name="'+$(this).attr('name')+'" value="'+$(this).find('option:selected').attr('value')+'" />');
      elt.insertAfter($(this));
      
      if ( $(this).val() )
      {
        form = $('.sf_admin_form form:first').parents().find('form');
        if ( $(this).attr('name') == 'manifestation[event_id]' )
          tmp = 'event';
        else
          tmp = 'location';
          
        args = form.attr('action',form.attr('action').indexOf('?') != -1
          ? form.attr('action')+'&'+tmp+'='+$(this).val()
          : form.attr('action')+'?'+tmp+'='+$(this).val());
      }
    }
  });
});

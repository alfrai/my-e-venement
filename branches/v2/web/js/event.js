$(document).ready(function(){
  $.get(manifestation_list_url,manifestation_list_loaded);
  $('#manifestation-new').click(manifestation_new_clicked);
});

function manifestation_list_loaded(data)
{
  $('#more .manifestation_list').html($(data).find(' .sf_admin_list'));
  $('#more .manifestation_list tfoot a[href]').click(function(){
    $.get($(this).attr('href'),manifestation_list_loaded);
    return false;
  });
}

function manifestation_new_clicked()
{
  form = $('.sf_admin_form form:first');
  anchor = $(this);
  $.post(form.attr('action'),form.serialize(),function(data){
    if ( $(data).find('.error').length > 0 )
    {
      // on event update error
      form.replaceWith($(data).find('.sf_admin_form form:first'));
      $('#sf_admin_form_tab_menu').tabs()
        .addClass('ui-tabs-vertical ui-helper-clearfix');
      $('#sf_admin_form_tab_menu li')
        .removeClass('ui-corner-top').addClass('ui-corner-all');
    }
    else
    {
      // on event update success
      window.location = anchor.attr('href');
    }
  });
  
  return false;
}


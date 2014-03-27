$(document).ready(function(){
  if ( typeof(manifestation_list_url) != 'undefined' )
  {
    $.get(manifestation_list_url,manifestation_list_loaded);
    $('#manifestation-new').click(manifestation_new_clicked);
  }
  
  // error management for embedded forms
  $('.sf_admin_form_field_Manifestations.ui-state-error [colspan="2"] > .error_list').remove();
  $('.sf_admin_form_field_Manifestations.ui-state-error :not([colspan="2"]) > .error_list').closest('table')
    .addClass('ui-state-error')
    .addClass('ui-widget-content')
    .addClass('ui-corner-all');
  setTimeout(function(){
    $('.sf_admin_form_field_Manifestations.ui-state-error').removeClass('ui-state-error')
      .find('.errors').remove();
  }, 1000);
  
});

function manifestation_list_loaded(data)
{
  data = $.parseHTML(data);
  
  $('#more .manifestation_list').html($(data).find(' .sf_admin_list'));
  $('#more .manifestation_list tfoot a[href]').click(function(){
    $.get($(this).prop('href'),manifestation_list_loaded);
    return false;
  });
  
  gauge_small();
}

function manifestation_new_clicked()
{
  form = $('.sf_admin_form form:first');
  if ( form.lenfth > 0 )
  {
    anchor = $(this);
    $.post(form.prop('action'),form.serialize(),function(data){
      data = $.parseHTML(data);
      
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
        window.location = anchor.prop('href');
      }
    });
    return false;
  }
  return true;
}

function checkpoint_autocompleter()
{
  jQuery(id+' input[name="autocomplete_checkpoint[organism_id]"]')
  .autocomplete(url, jQuery.extend({}, {
    dataType: 'json',
    parse:    function(data) {
      var parsed = [];
      for (key in data) {
        parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
      }
      return parsed;
    }
  }, { }))
  .result(function(event, data) { jQuery(id+' input[name="checkpoint[organism_id]"]').val(data[1]); });
}

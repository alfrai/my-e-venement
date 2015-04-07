$(document).ready(function(){
  if ( typeof(manifestation_list_url) != 'undefined' )
  {
    $.get(manifestation_list_url,LI.manifestation_list_loaded);
    $('#manifestation-new, #manifestations-import-ics').click(LI.manifestation_new_clicked);
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

if ( LI == undefined )
  var LI = {};

LI.manifestation_list_loaded = function(data)
{
  data = $.parseHTML(data);
  
  $('#more .manifestation_list').html($(data).find(' .sf_admin_list'));
  $('#more .manifestation_list tfoot a[href]').click(function(){
    $.get($(this).prop('href'),LI.manifestation_list_loaded);
    return false;
  });
  
  $('#more .manifestation_list .sf_admin_td_actions a').each(function(){ $(this).prop('title', $(this).text()); });
  
  gauge_small();
}

LI.manifestation_new_clicked = function()
{
  var form = $('.sf_admin_form form:first');
  if ( form.length > 0 )
  {
    var anchor = $(this);
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

LI.checkpoint_autocompleter = function()
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

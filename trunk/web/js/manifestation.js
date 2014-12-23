$(document).ready(function(){
  // fix the event_id, a manifestation cannot change the event it belongs to
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
  
  // set titles on contacts in the tickets list
  $('#sf_fieldset_tickets .contact a').each(function(){
    $(this).prop('title',$(this).html());
  });

  // select the locations' list in one click, or revert the current choice
  $('#sf_fieldset_resources .sf_admin_form_field_booking_list .label label')
    .prepend('<input type="checkbox" name="revert_selection" value="" /> ');
  $('#sf_fieldset_resources .sf_admin_form_field_booking_list [name="revert_selection"]').click(function(){
    $('#sf_fieldset_resources .sf_admin_form_field_booking_list input[type=checkbox]').each(function(){
      $(this).prop('checked',!$(this).prop('checked'));
    });
  });
  
  // two fields for applicant : minimize that !
  if ( $('#sf_fieldset_resources .sf_admin_form_field_contact_id').length > 0
    && $('#sf_fieldset_resources .sf_admin_form_field_applicant a').length > 0 )
  {
    $('#sf_fieldset_resources .sf_admin_form_field_contact_id.sf_admin_foreignkey input[type=text]')
      .after($('#sf_fieldset_resources .sf_admin_form_field_applicant a')
        .each(function(){ $(this).prop('title',$(this).html()); })
        .prepend('<span class="ui-icon ui-icon-person"></span>')
      );
    $('#sf_fieldset_resources .sf_admin_form_field_applicant').hide();
  }
  else
  if ( $('#sf_fieldset_resources .sf_admin_form_field_contact_id').length == 0
    && $('#sf_fieldset_resources .sf_admin_form_field_applicant a').length == 0 )
  {
    // if no applicant, then remove the field from the form
    $('#sf_fieldset_resources .sf_admin_form_field_applicant').hide();
  }
  
  // add titles on extra-informations fields
  $('#sf_fieldset_extra_informations table table tr').each(function(){
    $(this).find('td').prop('title',$(this).find('th label').html());
  });
  
  // highlight the extra-information fieldset if some information is present here
  if ( $.trim($('#sf_fieldset_extra_informations .sf_admin_form_field_description div:not(.label)').html())
    || $.trim($('#sf_fieldset_extra_informations .sf_admin_form_field_description textarea').val())
    || $('.sf_admin_form_field_ExtraInformations.show table tr').length > 0
    || $('.sf_admin_form_field_ExtraInformations table table').length > 3 )
  {
    $('.ui-tabs-nav [href="#sf_fieldset_extra_informations"]')
      .prepend('<span class="ui-icon ui-icon-alert floatright"></span>');
  }
  
  // PriceGauge sensitivity
  $('.sf_admin_form .sf_admin_form_field_gauges_prices').click(function(){
    $(this).addClass('ui-state-highlight');
  });
  
  // click on the first gauge on loading
  if ( location.hash == '#sf_fieldset_workspaces' )
  {
    var click_gauge = function(){
      setTimeout(function(){
        console.error('timeout');
        if ( $('#sf_fieldset_workspaces .gauges-all .gauge').length == 0 )
          click_gauge();
        else
          $('#sf_fieldset_workspaces .gauges-all .gauge').click();
      },500);
    }
    click_gauge();
  }
});

if ( LI == undefined )
  var LI = {};
if ( LI.seatedPlanInitializationFunctions == undefined )
  LI.seatedPlanInitializationFunctions = [];  
LI.seatedPlanInitializationFunctions.push(function(){
  $('.seated-plan .seat.txt').unbind('contextmenu');
});

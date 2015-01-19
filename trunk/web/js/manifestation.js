$(document).ready(function(){
  // find the best seats available for manifesations in batch
  $('.sf_admin_actions').closest('form').submit(function(){
    if ( $(this).find('select[name=batch_action]').val() != 'batchBestFreeSeat' )
      return;
    $.ajax({
      url: $(this).prop('action'),
      data: $(this).serialize(),
      method: window.location.hash == '#debug' ? 'get' : $(this).prop('method'),
      success: function(data){
        if ( window.location.hash == '#debug' )
          console.error(data);
        
        var container;
        $('<div></div>').addClass('ui-widget').addClass('ui-widget-content').addClass('ui-corner-all').prop('id', 'seats').addClass('sf_admin_list')
          .prepend($('<div class="ui-widget-header ui-corner-top fg-toolbar"></div>').prepend($('<h2></h2>').text($('.sf_admin_actions select[name=batch_action] option:selected').text())))
          .append(container = $('<table></table>').addClass('ui-widget-content'))
          .appendTo('body')
        ;
        
        $.each(data, function(id, seat){
          $('<tr></tr>').attr('data-id', seat.id).appendTo(container).addClass('sf_admin_row').addClass('ui-widget-content')
            .append($('<td></td>').addClass('rank').text(seat.rank))
            .append($('<td></td>').addClass('event').text(seat.event))
            .append($('<td></td>').append($('<a></a>').prop('href', seat.manifestation_url).text(seat.happens_at_txt)))
            .append($('<td></td>').addClass('gauge').html(seat.workspaces.replace("\n",'<br/>')))
            .append($('<td></td>').append($('<a></a>').addClass('name').prop('href', seat.sell_url).text(seat.name)))
          ;
        });
        
        $('<a></a>').addClass('close')
          .prop('href', '#close')
          .text('x')
          .click(function(){
            $('#seats').fadeOut(function(){ $(this).remove(); });
            $('#transition .close').click();
            return false;
          })
          .appendTo($('#seats h2'))
        ;
      }
    });
    return false;
  });
  
  // change the event of manifesations in batch
  $('.sf_admin_actions select').change(function(){
    if ( $(this).val() != 'batchChangeEvent' )
    {
      $('.sf_admin_batch_actions_choice [name=batch_event_id]').remove();
      return;
    }
    
    var url = $('#url_manifestation_filters_event_id').prop('href');
    $.ajax({
      url: url,
      data: { limit: 500, with_meta_event: true },
      success: function(data){
        // nothing to populate
        if ( data.length == 0 )
          return;
        
        // the select DOM object
        var select = $('<select><option></option></select>')
          .prop('name', 'batch_event_id')
          .insertBefore($('.sf_admin_batch_actions_choice input[type=submit]'));
        
        // ordering the data alphabetically then populate it into the previous <select>
        var arr = [];
        $.each(data, function(id, name){
          arr.push({id: id, name: name});
        });
        $.each(arr.sort(function(a,b){
          return $.trim(a.name.toLowerCase()) > $.trim(b.name.toLowerCase()) ? 1 : -1;
        }), function(i, event){
          $('<option></option>').val(event.id).text(event.name)
            .appendTo(select);
        });
      }
    });
  });
  
  // fix the event_id, a manifestation cannot change the event it belongs to
  $('select[name="manifestation[event_id]"]').each(function(){
    if ( $(this).find('option[selected=selected]').length > 1 )
    if ( $(this).val() != '' )
    {
      $(this).prop('disabled','disabled');
      $('<input type="hidden" />')
        .prop('name', $(this).prop('name'))
        .prop('value', $(this).find('option:selected').prop('value'))
        .insertAfter($(this))
      ;
      
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
  $.each({ contact_id: 'applicant', organism_id: 'applicant_organism' }, function(id, field){
  if ( $('#sf_fieldset_resources .sf_admin_form_field_'+id).length > 0
    && $('#sf_fieldset_resources .sf_admin_form_field_'+field+' a').length > 0 )
  {
    $('#sf_fieldset_resources .sf_admin_form_field_'+id+'.sf_admin_foreignkey input[type=text]')
      .after($('#sf_fieldset_resources .sf_admin_form_field_'+field+' a')
        .each(function(){ $(this).prop('title',$(this).html()); })
        .prepend('<span class="ui-icon ui-icon-person"></span>')
      );
    $('#sf_fieldset_resources .sf_admin_form_field_'+field).hide();
  }
  else
  if ( $('#sf_fieldset_resources .sf_admin_form_field_'+id).length == 0
    && $('#sf_fieldset_resources .sf_admin_form_field_'+field+' a').length == 0 )
  {
    // if no applicant, then remove the field from the form
    $('#sf_fieldset_resources .sf_admin_form_field_'+field).hide();
  }
  });
  
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

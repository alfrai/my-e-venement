// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};
  
$(document).ready(function(){
  // padding with "left 0" every two digits input[type=text]
  $('input[type=text][size=2]').each(function(){
    if ( parseInt($(this).val(), 10)+"" == ""+$(this).val() )
      $(this).val(LI.pad_num($(this).val(),2));
  });
  
  // transforming seconds into HH:ii
  LI.manifestation_duration();
  LI.event_close_before();

  // if duration or happens_at change, updating the ends_at date (for coherence only)
  $('.sf_admin_form_field_duration input[type=text]').change(function(){
    arr = /(\d+):(\d{1,2})/.exec($(this).val());
    if ( arr && LI.manifestation_datetime('happens_at')+'' !== 'Invalid Date' )
    {
      var before = LI.manifestation_datetime('ends_at');
      LI.manifestation_datetime('ends_at', new Date(
        Date.parse(LI.manifestation_datetime('happens_at')) +
        (parseInt(arr[1],10)*3600 + parseInt(arr[2],10)*60)*1000
      ));
      
      // follow the changes for the reservation
      if ( before.getTime() === before.getTime() ) // if date is valid
      {
        var diff = LI.manifestation_datetime('reservation_ends_at')+(LI.manifestation_datetime('ends_at')-before);
        LI.manifestation_datetime('reservation_ends_at', new Date( Date.parse(LI.manifestation_datetime('reservation_ends_at')) + diff ));
      }
    }
    LI.manifestation_coherence();
  }).change();
  
  $('.sf_admin_form_field_happens_at input[type=text]').change(function(e){
    if ( LI.manifestation_datetime('happens_at')+'' === 'Invalid Date' )
      return;
    
    // follow the changes for the reservation
    var arr = /(\d+):(\d{1,2})/.exec($('.sf_admin_form_field_duration input[type=text]').val());
    var diff = (parseInt(arr[1],10)*3600 + parseInt(arr[2],10)*60)*1000-(LI.manifestation_datetime('ends_at')-LI.manifestation_datetime('happens_at'));
    LI.manifestation_datetime('reservation_begins_at', new Date( Date.parse(LI.manifestation_datetime('reservation_begins_at')) + diff ));
    
    $('.sf_admin_form_field_duration input[type=text]').change();
  });
  
  // if ends_at changes, updating the duration
  $('.sf_admin_form_field_ends_at input[type=text]').change(function(){
    if ( LI.manifestation_datetime('ends_at')+'' === 'Invalid Date' || LI.manifestation_datetime('happens_at')+'' === 'Invalid Date' )
      return;
    
    // follow the changes for the reservation
    var arr = /(\d+):(\d{1,2})/.exec($('.sf_admin_form_field_duration input[type=text]').val());
    var diff = (parseInt(arr[1],10)*3600 + parseInt(arr[2],10)*60)*1000-(LI.manifestation_datetime('ends_at')-LI.manifestation_datetime('happens_at'));
    LI.manifestation_datetime('reservation_ends_at', new Date( Date.parse(LI.manifestation_datetime('reservation_ends_at')) - diff ));
    
    LI.manifestation_duration( (Date.parse(LI.manifestation_datetime('ends_at')) - Date.parse(LI.manifestation_datetime('happens_at'))) / 1000);
    
    LI.manifestation_coherence();
  });
  
  // anticipating the model's logical constrainsts (here for reservations)
  $('.sf_admin_form_field_reservation_begins_at input[type=text], .sf_admin_form_field_reservation_ends_at input[type=text]')
    .change(LI.manifestation_coherence);
  
  // adding the "all day" option
  $('<span></span>').addClass('allday')
    .append('<input type="checkbox" id="allday" name="allday" value="allday" />')
    .append(' ')
    .append($('<label for="allday"></label>').text($('#more .allday').text()))
    .appendTo($('.sf_admin_form_field_happens_at .widget'))
    .find('input').change(function(e){
      var happens = $(this).closest('.sf_admin_date');
      var ends  = $(this).closest('.sf_admin_form_row').siblings('.sf_admin_form_field_ends_at');
      if ( $(this).is(':checked') )
      {
        happens.find('.time input').val('00').change();
        ends.find('.time input:first').val('23').change();
        ends.find('.time input:last').val('59').change();
        LI.manifestation_datetime('reservation_ends_at', LI.manifestation_datetime('ends_at'));
        LI.manifestation_datetime('reservation_begins_at', LI.manifestation_datetime('happens_at'));
        $.each([happens.find('.time'), ends.find('.time'), $('.sf_admin_form_field_reservation_begins_at'), $('.sf_admin_form_field_reservation_ends_at')], function(id, elt){
          elt.fadeOut('slow');
        });
      }
      else
      {
        $.each([happens.find('.time'), ends.find('.time'), $('.sf_admin_form_field_reservation_begins_at'), $('.sf_admin_form_field_reservation_ends_at')], function(id, elt){
          elt.fadeIn('slow');
        });
      }
    }).click(function(){
      if ( $(this).is(':checked') && !confirm($('#more .are-you-sure').text()) )
        return false;
    });
  $('.sf_admin_form_field_happens_at .widget').each(function(){
    var ends = $(this).closest('.sf_admin_form_row').siblings('.sf_admin_form_field_ends_at');
    if ( parseInt($(this).find('.time input:first').val()) == 0 && parseInt($(this).find('.time input:last').val()) == 0
      && parseInt(ends.find('.time input:first').val()) == '23' && parseInt(ends.find('.time input:last').val()) == '59' )
    {
      $(this).find('.allday input').prop('checked', true).change();
    }
  });
});

LI.manifestation_duration = function(duration = null)
{
  // setting the new duration if given
  if ( !isNaN(parseInt(duration,10)) )
    $('.sf_admin_form_field_duration input[type=text]').val(duration);
  
  // converting seconds into HH:ii
  $('.sf_admin_form_field_duration input[type=text]').each(function(){
    if ( $(this).val().indexOf(':') < 0 )
    if ( !isNaN(parseInt($(this).val(),10)) ) // if is a number
      $(this).val(Math.floor(parseInt($(this).val(),10)/3600)+':'+('0'+Math.floor(parseInt($(this).val(),10)%3600/60)).slice(-2));
  });
  
  return duration;
}
LI.event_close_before = function(close_before = null)
{
  // setting the new close_before if given
  if ( !isNaN(parseInt(close_before,10)) )
    $('.sf_admin_form_field_close_before input[type=text]').val(close_before);
  
  // converting seconds into HH:ii
  $('.sf_admin_form_field_close_before input[type=text]').each(function(){
    if ( $(this).val().indexOf(':') < 0 )
    if ( !isNaN(parseInt($(this).val(),10)) ) // if is a number
      $(this).val(Math.floor(parseInt($(this).val(),10)/3600)+':'+('0'+Math.floor(parseInt($(this).val(),10)%3600/60)).slice(-2));
  });
  
  return close_before;
}

// errors/coherence anticipation ...
LI.manifestation_coherence = function()
{
  if ( LI.manifestation_datetime('reservation_begins_at')+'' === 'Invalid Date'
    && LI.manifestation_datetime('happens_at')           +'' !== 'Invalid Date' )
    LI.manifestation_datetime('reservation_begins_at', LI.manifestation_datetime('happens_at'));
  if ( LI.manifestation_datetime('reservation_ends_at')  +'' === 'Invalid Date'
    && LI.manifestation_datetime('ends_at')              +'' !== 'Invalid Date' )
    LI.manifestation_datetime('reservation_ends_at', LI.manifestation_datetime('ends_at'));
  
  LI._manifestation_coherence('reservation_begins_at',  '<=', 'happens_at');
  LI._manifestation_coherence('reservation_ends_at',    '>=', 'ends_at');
  LI._manifestation_coherence('ends_at',                '>=', 'happens_at', 'duration');
}

LI._manifestation_coherence = function(field1, operand, field2, extrafield = null)
{
  if ( LI.manifestation_datetime(field1)+'' === 'Invalid Date'
    || LI.manifestation_datetime(field2)+'' === 'Invalid Date' )
    return false;
  
  var bool;
  switch ( operand ) {
  case '>':
    bool = LI.manifestation_datetime(field1) >  LI.manifestation_datetime(field2);
    break;
  case '<':
    bool = LI.manifestation_datetime(field1) <  LI.manifestation_datetime(field2);
    break;
  case '>=':
    bool = LI.manifestation_datetime(field1) >= LI.manifestation_datetime(field2);
    break;
  case '<=':
    bool = LI.manifestation_datetime(field1) <= LI.manifestation_datetime(field2);
    break;
  default:
    bool = LI.manifestation_datetime(field1) == LI.manifestation_datetime(field2);
  }
  
  if ( bool )
  {
    $('.sf_admin_form_field_'+field1).removeClass('ui-state-error');
    if ( extrafield )
      $('.sf_admin_form_field_'+extrafield).removeClass('ui-state-error');
  }
  else
  {
    $('.sf_admin_form_field_'+field1).addClass('ui-state-error').addClass('ui-corner-all');
    if ( extrafield )
      $('.sf_admin_form_field_'+extrafield).addClass('ui-state-error').addClass('ui-corner-all');
  }
}

LI.manifestation_datetime = function(name = 'happens_at', value = null)
{
  if ( value )
  {
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][year]"]').val(value.getFullYear());
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][month]"]').val(LI.pad_num(value.getMonth()+1,2));
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][day]"]').val(LI.pad_num(value.getDate(),2));
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][hour]"]').val(LI.pad_num(value.getHours(),2));
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][minute]"]').val(LI.pad_num(value.getMinutes(),2));
  }
  
  return new Date(
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][year]"]').val(),10),
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][month]"]').val(),10) - 1,
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][day]"]').val(),10),
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][hour]"]').val(),10),
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][minute]"]').val(),10)
  );
}

LI.manifestation_check_resource = function(elt = NULL)
{
  // not a blocking booking
  if ( $('input[name="manifestation[blocking]"]:checked').length == 0 )
  {
    $('.sf_admin_form_field_booking_list li.ui-state-error')
      .removeClass('ui-state-error')
      .find('.error.conflict').remove();
    $('.sf_admin_form_field_location_id').each(function(){
      if ( $(this).find('input[value=""]').length > 0 )
        return;
      $(this).removeClass('ui-state-error')
        .find('.error.conflict').remove();
    });
    return;
  }
  
  var start = LI.manifestation_datetime('reservation_begins_at').getTime()/1000;
  var stop  = LI.manifestation_datetime('reservation_ends_at').getTime()/1000;
  var location_id = $(elt).val();
  if ( !start || !stop || !location_id )
    return;
  
  $.get(LI.data.url, {
    start: start,
    end: stop,
    conflicts: true,
    location_id: location_id,
    no_ids: LI.data.no_ids,
    only_blocking: true,
  }, function(data)
  {
    if ( data.length > 0 )
    {
      $(elt).parent().find('.error.conflict').remove();
      for ( i = 0 ; i < data.length ; i++ )
      if ( parseInt(data[i].id)+'' === ''+data[i].id )
      {
        $(elt).parent().append($('<div class="error conflict"><a></a></div>')).find('.error.conflict a')
          .html(data[i].title)
          .prop('href', data[i].hackurl)
          ;
      }
      $(elt).parent().addClass('ui-state-error').addClass('ui-corner-all');
    }
    else
    {
      $(elt).parent().removeClass('ui-state-error')
        .find('.error.conflict').remove();
    }
  }, 'json');
}

$(document).ready(function(){
  // transforming seconds into HH:ii
  li_manifestation_duration();

  // if duration or happens_at change, updating the ends_at date (for coherence only)
  $('.sf_admin_form_field_duration input[type=text]').change(function(){
    arr = /(\d+):(\d{1,2})/.exec($(this).val());
    if ( arr && li_manifestation_datetime('happens_at')+'' !== 'Invalid Date' )
      li_manifestation_datetime('ends_at', new Date(
        Date.parse(li_manifestation_datetime('happens_at')) +
        (parseInt(arr[1],10)*3600 + parseInt(arr[2],10)*60)*1000
      ));
    li_manifestation_coherence();
  }).change();
  $('.sf_admin_form_field_happens_at input[type=text]').change(function(){
    $('.sf_admin_form_field_duration input[type=text]').change();
  });
  
  // if ends_at changes, updating the duration
  $('.sf_admin_form_field_ends_at input[type=text]').change(function(){
    field = $(this).closest('.sf_admin_form_row');
    if ( li_manifestation_datetime('ends_at')+'' !== 'Invalid Date' && li_manifestation_datetime('happens_at')+'' !== 'Invalid Date' )
      li_manifestation_duration( (Date.parse(li_manifestation_datetime('ends_at')) - Date.parse(li_manifestation_datetime('happens_at'))) / 1000);      
    li_manifestation_coherence();
  });
  
  // anticipating the model's logical constrainsts (here for reservations)
  $('.sf_admin_form_field_reservation_begins_at input[type=text], .sf_admin_form_field_reservation_ends_at input[type=text]')
    .change(li_manifestation_coherence);
});

function li_manifestation_duration(duration = null)
{
  // setting the new duration if given
  if ( !isNaN(parseInt(duration,10)) )
    $('.sf_admin_form_field_duration input[type=text]').val(duration);
  
  // converting seconds into HH:ii
  $('.sf_admin_form_field_duration input[type=text]').each(function(){
    if ( !isNaN(parseInt($(this).val(),10)) ) // if is a number
      $(this).val(Math.floor(parseInt($(this).val(),10)/3600)+':'+('0'+Math.floor(parseInt($(this).val(),10)%3600/60)).slice(-2));
  });
  
  return duration;
}

// errors/coherence anticipation ...
function li_manifestation_coherence()
{
  if ( li_manifestation_datetime('reservation_begins_at')+'' === 'Invalid Date'
    && li_manifestation_datetime('happens_at')           +'' !== 'Invalid Date' )
    li_manifestation_datetime('reservation_begins_at', li_manifestation_datetime('happens_at'));
  if ( li_manifestation_datetime('reservation_ends_at')  +'' === 'Invalid Date'
    && li_manifestation_datetime('ends_at')              +'' !== 'Invalid Date' )
    li_manifestation_datetime('reservation_ends_at', li_manifestation_datetime('ends_at'));
  
  _li_manifestation_coherence('reservation_begins_at',  '<=', 'happens_at');
  _li_manifestation_coherence('reservation_ends_at',    '>=', 'ends_at');
  _li_manifestation_coherence('ends_at',                '>=', 'happens_at', 'duration');
}

function _li_manifestation_coherence(field1, operand, field2, extrafield = null)
{
  if ( li_manifestation_datetime(field1)+'' === 'Invalid Date'
    || li_manifestation_datetime(field2)+'' === 'Invalid Date' )
    return false;
  
  var bool;
  switch ( operand ) {
  case '>':
    bool = li_manifestation_datetime(field1) >  li_manifestation_datetime(field2);
    break;
  case '<':
    bool = li_manifestation_datetime(field1) <  li_manifestation_datetime(field2);
    break;
  case '>=':
    bool = li_manifestation_datetime(field1) >= li_manifestation_datetime(field2);
    break;
  case '<=':
    bool = li_manifestation_datetime(field1) <= li_manifestation_datetime(field2);
    break;
  default:
    bool = li_manifestation_datetime(field1) == li_manifestation_datetime(field2);
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

function li_manifestation_datetime(name = 'happens_at', value = null)
{
  if ( value )
  {
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][year]"]').val(value.getFullYear());
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][month]"]').val(value.getMonth()+1);
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][day]"]').val(value.getDate());
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][hour]"]').val(value.getHours());
    $('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][minute]"]').val(value.getMinutes());
  }
  
  return new Date(
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][year]"]').val(),10),
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][month]"]').val(),10) - 1,
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][day]"]').val(),10),
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][hour]"]').val(),10),
    parseInt($('.sf_admin_form_field_'+name+' input[name="manifestation['+name+'][minute]"]').val(),10)
  );
}

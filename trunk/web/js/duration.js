$(document).ready(function(){
  // ends_at field... which is only a JS trick
  li_manifestation_ends_at(new Date(
    Date.parse(li_manifestation_happens_at()) +
    parseInt($('.sf_admin_form_field_duration input[name="manifestation[duration]"]').val(),10) * 1000
  ));
  
  // if ends_at changes, updating the duration
  $('.sf_admin_form_field_ends_at input[type=text]').change(function(){
    field = $(this).closest('.sf_admin_form_row');
    go = true;
    field.find('input[type=text]').each(function(){
      if ( isNaN(parseInt($(this).val(),10)) )
        go = false;
    });
    if ( go )
      li_manifestation_duration( (Date.parse(li_manifestation_ends_at()) - Date.parse(li_manifestation_happens_at())) / 1000);      
  });
  
  // if duration changes, updating the ends_at date (for coherence only)
  $('.sf_admin_form_field_duration input[type=text]').change(function(){
    arr = /(\d+):(\d{1,2})/.exec($(this).val());
    if ( arr )
    {
      duration = parseInt(arr[1],10)*3600 + parseInt(arr[2],10)*60;
      li_manifestation_ends_at(new Date(Date.parse(li_manifestation_happens_at()) + duration*1000));
    }
  });
  
  // transforming seconds into HH:ii
  li_manifestation_duration();
});

function li_manifestation_duration(duration = null)
{
  if ( !isNaN(parseInt(duration,10)) )
    $('.sf_admin_form_field_duration input[type=text]').val(duration);
  
  $('.sf_admin_form_field_duration input[type=text]').each(function(){
    $(this).val(Math.floor(parseInt($(this).val(),10)/3600)+':'+('0'+Math.floor(parseInt($(this).val(),10)%3600/60)).slice(-2));
  });
  
  return duration;
}

function li_manifestation_happens_at()
{
  return new Date(
    parseInt($('.sf_admin_form_field_happens_at input[name="manifestation[happens_at][year]"]').val(),10),
    parseInt($('.sf_admin_form_field_happens_at input[name="manifestation[happens_at][month]"]').val(),10),
    parseInt($('.sf_admin_form_field_happens_at input[name="manifestation[happens_at][day]"]').val(),10),
    parseInt($('.sf_admin_form_field_happens_at input[name="manifestation[happens_at][hour]"]').val(),10),
    parseInt($('.sf_admin_form_field_happens_at input[name="manifestation[happens_at][minute]"]').val(),10)
  );
}
function li_manifestation_ends_at(ends_at = null)
{
  if ( ends_at )
  {
    $('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][year]"]').val(ends_at.getFullYear());
    $('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][month]"]').val(ends_at.getMonth());
    $('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][day]"]').val(ends_at.getDate());
    $('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][hour]"]').val(ends_at.getHours());
    $('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][minute]"]').val(ends_at.getMinutes());
  }
  
  return new Date(
    parseInt($('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][year]"]').val(),10),
    parseInt($('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][month]"]').val(),10),
    parseInt($('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][day]"]').val(),10),
    parseInt($('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][hour]"]').val(),10),
    parseInt($('.sf_admin_form_field_ends_at input[name="manifestation[ends_at][minute]"]').val(),10)
  );
}

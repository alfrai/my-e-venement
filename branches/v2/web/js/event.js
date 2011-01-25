function manifestations_loaded(data)
{
  $('#more .manifestations').html($(data).find(' .sf_admin_list'));
  $('#more .manifestations tfoot a[href]').click(function(){
    $.get($(this).attr('href'),group_manifestations_loaded);
    return false;
  });
}

$(document).ready(function(){
  $.get(manifestations_url,manifestations_loaded);
});

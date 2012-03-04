function group_contacts_loaded(data)
{
  $('#more .contacts').html($(data).find(' .sf_admin_list'));
  $('#more .contacts tfoot a[href]').click(function(){
    $.get($(this).attr('href'),group_contacts_loaded);
    return false;
  });
}

$(document).ready(function(){
  $.get(group_contacts_url,group_contacts_loaded);
});

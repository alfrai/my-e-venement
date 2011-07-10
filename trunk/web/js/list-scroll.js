$(document).ready(function(){
  list_scroll();
});
function list_scroll()
{
  $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){
    $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){return false;});
    $.get($(this).attr('href'),function(data){
      $('.sf_admin_list > table > tbody').append($(data).find('.sf_admin_list > table > tbody tr.sf_admin_row'));
      $('#sf_admin_pager')
        .replaceWith($(data).find('#sf_admin_pager'));
      list_scroll();
    });
    return false;
  });
}

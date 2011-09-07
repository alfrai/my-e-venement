$(document).ready(function(){
  list_add_actions_titles();
  list_scroll();
});
function list_scroll()
{
  $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){
    $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){return false;});
    $.get($(this).attr('href'),function(data){
      $('.sf_admin_list > table > tbody').append($(data).find('.sf_admin_list > table > tbody tr.sf_admin_row')
        .mouseenter(function(){
          $(this).addClass('ui-state-hover');
        })
        .mouseleave(function(){
          $(this).removeClass('ui-state-hover');
        }));
      $('#sf_admin_pager')
        .replaceWith($(data).find('#sf_admin_pager'));
      list_add_actions_titles();
      list_scroll();
    });
    return false;
  });
}
function list_add_actions_titles()
{
  $('.sf_admin_td_actions a').each(function(){
    elt = $(this).clone(true);
    elt.find('span').remove();
    $(this).attr('title',elt.html());
  });
}

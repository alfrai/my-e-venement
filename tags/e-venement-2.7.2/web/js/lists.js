$(document).ready(function(){
  $('.sf_admin_td_actions a').each(function(){
    elt = $(this).clone(true);
    elt.find('span').remove();
    $(this).prop('title',elt.html());
  });
});

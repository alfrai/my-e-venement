$(document).ready(function(){
  // hover on rows
  $('.summary tbody tr').mouseenter(function(){
    $(this).addClass('ui-state-hover');
  });
  $('.summary tbody tr').mouseleave(function(){
    $(this).removeClass('ui-state-hover');
  });
});

$(document).ready(function(){
  // doing a short effect on page unload
  $('a:not([href^=#]):not([target=_blank]):is('[href]').click(window_transition);
  $('form').submit(window_transition);
  $('#transition .close').click(function(){ $('#transition').fadeOut('medium'); });
  
  // changing menu
  $('#menu > li > span').mouseenter(function(){
    if ( $('#menu li.show').length > 0 && !$(this).parent().hasClass('show') )
      $(this).click();
  });
  // clicking everywhere but a menu
  $('body').click(function(){
    if ( $('#menu li:hover').length == 0 )
      $('#menu li').removeClass('show');
  });
  // clicking to any menu
  $('#menu > li').click(function(){
    show = !$(this).hasClass('show');
    $('#menu li').removeClass('show');
    if ( show )
      $(this).addClass('show');
  });
  // clicking to a submenu
  $('#menu .second > li > a, #menu .third > li > a').click(function(){
    $('#menu .show').removeClass('show');
  });
  // subsubmenu
  $('#menu .second a').mouseenter(function(){
    if ( $(this).parent().find('.third').length > 0 )
      $(this).parent().addClass('onit');
  })
  .mouseleave(function(){
    if ( $(this).parent().find('.third').length > 0 )
      $(this).parent().removeClass('onit');
  });
  $('#menu .third').mouseenter(function(){
    $(this).parent().addClass('onsub');
  })
  .mouseleave(function(){
    $(this).parent().removeClass('onsub');
  });
  
  $('#menu .fancybox').fancybox({
    width:  320,
    height: 630,
    onComplete: function(){ about_show_contributors() },
  });
  about_show_contributors();
});


function about_show_contributors()
{
  $('.ui-widget-about .show-contributors').unbind().click(function(){
    $(this).parent().parent().parent().parent().find('.contributors').fadeToggle('slow');
    return false;
  });
}

function window_transition(speed)
{
  if ( speed == 'undefined' )
    speed = 'medium';
  
  $('#transition').fadeIn(speed);
}

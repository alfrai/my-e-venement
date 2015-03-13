$(document).ready(function(){
  // hide the links' widget if no link is shown (and it is not a "debug environment")
  if ( !$('body').is('.env-debug') && $('.links .link').length == 0 )
    $('.links').hide();
  
  // the linked elements
  $('.links .link').click(function(){
    window.location = $(this).find('a').prop('href');
  });
  $('.links .link img').load(function(){
    if ( $(this).width() > $(this).parent().innerWidth() )
      $(this).width($(this).parent().innerWidth());
  }).load();
  
  // if a scrollbar appears, minimize it
  $('.links').niceScroll();
});

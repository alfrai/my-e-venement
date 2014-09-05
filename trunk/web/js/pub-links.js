$(document).ready(function(){
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

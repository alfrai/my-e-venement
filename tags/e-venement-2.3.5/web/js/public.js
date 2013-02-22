$(document).ready(function(){
  $.get($('#cart-widget-url').attr('href'),function(data){
    $('body').prepend($(data).find('#cart-widget'));
  });
});
        

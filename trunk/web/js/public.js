$(document).ready(function(){
  $.get($('#cart-widget-url').attr('href'),function(data){
    $('body').prepend($($.parseHTML(data)).find('#cart-widget'));
  });
});
        

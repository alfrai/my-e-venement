$(document).ready(function(){
  $.get($('#cart-widget-url').prop('href'),function(data){
    $('body').prepend($($.parseHTML(data)).find('#cart-widget'));
  });
});
        

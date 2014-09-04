$(document).ready(function(){
  $('form').submit(function(){
    $.post($(this).prop('action'), $(this).serialize(), function(json){
      $('#transition .close').click();
      $.each(['success', 'error'], function(key, value){
        if ( json[value] )
          LI.alert(json[value].message, value);
      });
    });
    return false;
  });
});

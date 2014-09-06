$(document).ready(function(){
  $('form').submit(function(){
    if ( window.location.hash == '#debug' )
    {
      $(this).append('<input type="hidden" name="debug" value="" />')
        .prop('target', '_blank');
      $('#transition .close').click();
      return true;
    }
    
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

$(document).ready(function(){
  $('form input, form textarea').focusin(function(){
    $(this).parent().addClass('nolabel');
  }).focusout(function(){
    if ( !$(this).val() )
      $(this).parent().removeClass('nolabel');
  }).each(function(){
    $(this).parent().find('label').css('max-width', $(this).width()+'px');
  });
  $('form span label').click(function(){
    $(this).siblings('input:not([type=hidden]), textarea').focus();
  });
  
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
      location.reload();
    });
    return false;
  });
});

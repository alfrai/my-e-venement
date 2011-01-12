$(document).ready(function(){
  if ( $('.check input').length == $('.check input:checked').length )
    $('#select-all').click();
  $('#select-all').click(function(){
    if ( $(this).attr('checked') )
      $('.check input').attr('checked','checked');
    else
      $('.check input').removeAttr('checked');
    
    return true;
  });
});

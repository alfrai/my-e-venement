$(document).ready(function(){
  $('[type=text][value=""]:first').focus();
  
  $('[name=all][type=checkbox]').click(function(){
    $('[type=text]:first').focus();
  });
  
  $('[type=text]').keypress(function(e){
    if ( e.which == 13 )
      return validate_rfid($(this).val());
  });
});

function validate_rfid(rfid)
{
  if ( $('[type=text][value=""]:first').length == 0 )
    return true;
  
  $('[type=text][value=""]:first').focus();
  if ( $('[name=all][type=checkbox]:checked').length > 0 )
  {
    $('[type=text][value=""]:first').val(rfid);
    validate_rfid(rfid);  
  }
}

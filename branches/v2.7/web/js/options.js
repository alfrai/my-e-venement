$(document).ready(function(){
  if ( $('.check input').length == $('.check input:checked').length )
    $('#select-all').click();
  
  $('#select-all').click(function(){
    if ( $(this).prop('checked') )
      $('.check input').prop('checked','checked');
    else
      $('.check input').removeAttr('checked');
    
    return true;
  });
  
  if ( $('.sf_admin_flashes > *').length == 0 )
    $('.sf_admin_flashes').remove();
  else
    setTimeout(function(){ $('.sf_admin_flashes > .notice').fadeOut('slow'); },3000);
});

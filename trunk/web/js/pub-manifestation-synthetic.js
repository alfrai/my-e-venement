$(document).ready(function(){
  LI.pubNamedTicketsInitialization();
  
  $('#categories form').submit(function(){
    $.ajax({
      type: $(this).prop('method'),
      url:  $(this).prop('action'),
      data: $(this).serialize(),
      success: function(json){
        if ( json.error && json.error.message )
          LI.alert(json.error.message, 'error');
        if ( json.success && json.success.message )
          LI.alert(json.success.message, 'success');
        LI.pubNamedTicketsInitialization();
      }
    });
    return false;
  });
});

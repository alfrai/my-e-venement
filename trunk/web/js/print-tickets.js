$(document).ready(function(){
  window.print();
  
  // update the parent window's content
  window.opener.li.initContent();
  
  // print again
  if ( $('#options #print-again').length > 0 )
    window.location = $('#options #print-again a').prop('href');
  
  // close
  if ( $('#options #close').length > 0 && $('#options #print-again').length == 0 )
    window.close();
});

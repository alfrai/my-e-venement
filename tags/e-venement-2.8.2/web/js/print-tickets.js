$(document).ready(function(){
  // removing some column headers when there is at least one product
  if ( $('#lines tbody tr.product').length > 0 )
    $('#lines').addClass('with-product');
  
  window.print();
  
  // update the parent window's content
  if ( window.opener != undefined && typeof window.opener.li === 'object' )
    window.opener.li.initContent();
  
  // print again
  if ( $('#options #print-again').length > 0 )
    window.location = $('#options #print-again a').prop('href');
  
  // close
  if ( $('#options #close').length > 0 && $('#options #print-again').length == 0 )
    window.close();
});

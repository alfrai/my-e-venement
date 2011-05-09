$(document).ready(function(){
  $('#ledger .method .see-more a').unbind().click(function(){
    $('#ledger .payment.method-'+parseInt($(this).attr('href').substring(1))).fadeToggle();
  });
  $('#ledger .method .see-more a').click();
  
  $('#ledger .event .see-more a').unbind().click(function(){
    $('#ledger .event-'+parseInt($(this).attr('href').substring(1))).fadeToggle();
  });
  $('#ledger .event .see-more a').click();
  
  $('#criterias .submit a').click(function(){
    $('#criterias').attr('action',$(this).attr('href'));
    $('#criterias').submit();
    return false;
  });
});

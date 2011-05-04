$(document).ready(function(){
  $('#ledger .method .see-more a').unbind().click(function(){
    $('#ledger .payment.method-'+parseInt($(this).attr('href').substring(1))).fadeToggle();
  });
  $('#ledger .method .see-more a').click();
});

$(document).ready(function(){
  // cash ledger / method
  $('#ledger .method .see-more a').unbind().click(function(){
    $('#ledger .payment.method-'+parseInt(/#(\d+)$/.exec($(this).prop('href'))[1],10)).fadeToggle();
    $(this).html($(this).html() == '-' ? '+' : '-');
  }).click().html('+');
  
  // sales ledger / manifs
  $('#ledger-products .declination .see-more a, #ledger-events .manif .see-more a').unbind().click(function(){
    var line = /#(\w[\w-]+-\w+)$/.exec($(this).attr('href'))[1];
    if ( $(this).html() == '+' )
      $(this).closest('table').find('.'+line).fadeIn();
    else
      $(this).closest('table').find('.'+line).fadeOut();
    $(this).html( $(this).html() == '-' ? '+' : '-' );
  });
  
  // sales ledger / events
  var links = $('#ledger-products .product .see-more a, #ledger-events .event .see-more a').unbind().click(function(){
    var line = /#(\w+-\d+)$/.exec($(this).prop('href'))[1];
    if ( $(this).html() == '+' )
    {
      $(this).closest('table').find('.'+line).each(function(){
        $(this).fadeIn();
      });
    }
    else
    {
      $(this).closest('table').find('.'+line).each(function(){
        $(this).fadeOut();
        if ( $(this).find('.see-more a').html() == '-' )
          $(this).find('.see-more a').click();
      });
    }
    $(this).html($(this).html() == '-' ? '+' : '-');
  }).click();
  
  // clicks for initial states
  //$('#ledger-products .product .see-more a, #ledger-events .event .see-more a').html('-');
  //$('#ledger-products .product .see-more a, #ledger-events .event .see-more a').click();
  
  // hover on rows
  $('#ledger-events tbody tr, #ledger-products tbody tr, #ledger tbody tr').mouseenter(function(){
    $(this).addClass('ui-state-hover');
  });
  $('#ledger-events tbody tr, #ledger-products tbody tr, #ledger tbody tr').mouseleave(function(){
    $(this).removeClass('ui-state-hover');
  });
});

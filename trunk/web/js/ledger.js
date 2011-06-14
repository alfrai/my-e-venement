$(document).ready(function(){
  // cash ledger / method
  $('#ledger .method .see-more a').unbind().click(function(){
    $('#ledger .payment.method-'+parseInt($(this).attr('href').substring(1))).fadeToggle();
    $(this).html($(this).html() == '-' ? '+' : '-');
  });
  $('#ledger .method .see-more a').html('-');
  $('#ledger .method .see-more a').click();
  
  // sales ledger / events
  $('#ledger .event .see-more a').html('-');
  $('#ledger .event .see-more a').unbind().click(function(){
    if ( $(this).html() == '+' )
    {
      $('#ledger .event-'+parseInt($(this).attr('href').substring(7))).each(function(){
        $(this).fadeIn();
      });
    }
    else
    {
      $('#ledger .event-'+parseInt($(this).attr('href').substring(7))).each(function(){
        $(this).fadeOut();
        if ( $(this).find('.see-more a').html() == '-' )
          $(this).find('.see-more a').click();
      });
    }
    $(this).html($(this).html() == '-' ? '+' : '-');
  });
  
  // sales ledger / manifs
  $('#ledger .manif .see-more a').unbind().click(function(){
    if ( $(this).html() == '+' )
      $('#ledger .manif-'+parseInt($(this).attr('href').substring(7))).fadeIn();
    else
      $('#ledger .manif-'+parseInt($(this).attr('href').substring(7))).fadeOut();
    $(this).html( $(this).html() == '-' ? '+' : '-' );
  });
  
  // clicks for initial states
  $('#ledger .event .see-more a').click();
  
  // hover on rows
  $('#ledger tbody tr').mouseenter(function(){
    $(this).addClass('ui-state-hover');
  });
  $('#ledger tbody tr').mouseleave(function(){
    $(this).removeClass('ui-state-hover');
  });
  
  // go to the other ledger
  $('#criterias .submit a').click(function(){
    $('#criterias').attr('action',$(this).attr('href'));
    $('#criterias').submit();
    return false;
  });
});

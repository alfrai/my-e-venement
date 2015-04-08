$(document).ready(function(){
  // cash ledger / method
  $('#ledger .method .see-more a').unbind().click(function(){
    $('#ledger .payment.method-'+parseInt(/#(\d+)$/.exec($(this).prop('href'))[1],10)).fadeToggle();
    $(this).html($(this).html() == '-' ? '+' : '-');
  });
  $('#ledger .method .see-more a').html('-');
  $('#ledger .method .see-more a').click();
  
  // sales ledger / events
  $('#ledger .event .see-more a').html('-');
  $('#ledger .event .see-more a').unbind().click(function(){
    event_id = parseInt(/#event-(\d+)/.exec($(this).prop('href'))[1],10);
    if ( $(this).html() == '+' )
    {
      $('#ledger .event-'+event_id).each(function(){
        $(this).fadeIn();
      });
    }
    else
    {
      $('#ledger .event-'+event_id).each(function(){
        $(this).fadeOut();
        if ( $(this).find('.see-more a').html() == '-' )
          $(this).find('.see-more a').click();
      });
    }
    $(this).html($(this).html() == '-' ? '+' : '-');
  });
  
  // sales ledger / manifs
  $('#ledger .manif .see-more a').unbind().click(function(){
    manif_id = parseInt(/#manif-(\d+)/.exec($(this).prop('href'))[1],10);
    if ( $(this).html() == '+' )
      $('#ledger .manif-'+manif_id).fadeIn();
    else
      $('#ledger .manif-'+manif_id).fadeOut();
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
});

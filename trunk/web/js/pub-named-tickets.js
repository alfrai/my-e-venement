// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  $('form.named-tickets').submit(function(){
    $.ajax({
      url: $(this).prop('action'),
      type: $(this).prop('method'),
      data: $(this).serialize(),
      success: LI.pubNamedTicketsData,
    });
    return false;
  });
});


LI.pubNamedTicketsInitialization = function()
{
  $.get($('form.named-tickets').prop('action'), LI.pubNamedTicketsData);
}
LI.pubNamedTicketsData = function(json)
{
  if (!( json.success && json.success.tickets ))
  {
    LI.alert('An error occurred with named tickets');
    return;
  }
  
  $('form.named-tickets .ticket:not(.sample)').remove();
  if ( json.success.tickets.length == 0 )
    $('form.named-tickets').fadeOut();
  else
    $('form.named-tickets').fadeIn();
  
  // reinit the previously selected seats
  $('.picture.seated-plan .seat.ordered.in-progress').removeClass('ordered').removeClass('in-progress');
  $('.picture.seated-plan .seat[data-ticket-id]').removeAttr('data-ticket-id');
      
  $.each(json.success.tickets, function(id, ticket){
    var elt = $('form.named-tickets .ticket.sample').clone(true)
      .removeClass('sample')
      .appendTo($('form.named-tickets'))
    ;
    $.each(['gauge_id', 'seat_id', 'price_id', 'contact_id'], function(key, field){
      elt.attr('data-'+field.replace('_','-'), ticket[field]);
    });
    $.each(['id', 'gauge_name', 'seat_name', 'value', 'taxes', 'contact_id', 'contact_name', 'contact_firstname', 'contact_email', 'comment'], function(key, field){
      if ( elt.find('.'+field+' input').length > 0 )
        elt.find('.'+field+' input').val(ticket[field]);
      else
        elt.find('.'+field).text(ticket[field]);
    });
    elt.find('input').each(function(){
      $(this).attr('name', $(this).attr('name').replace('%%ticket_id%%', ticket.id));
    });
    
    // synthetic view or not
    if ( ticket.prices_list.length == 0 )
      elt.find('.price_name').text(ticket.price_name);
    else
    {
      // display the currently selected seat
      $('.picture.seated-plan .seat[data-id='+ticket.seat_id+']')
        .attr('data-ticket-id', ticket.id)
        .addClass('ordered').addClass('in-progress');
      $('<option value=""></option>').text('--'+$('#plans .infos .no-price').text()+'--').appendTo(elt.find('.price_name select'));
      $.each(ticket.prices_list, function(id, name){
        $('<option></option>').val(id).text(name)
          .appendTo(elt.find('.price_name select'));
      });
      elt.find('.price_name select').val(ticket.price_id);
      elt.find('.price_name select, .delete, .me').each(function(){
        $(this).attr('name', $(this).attr('name').replace('%%ticket_id%%', ticket.id));
      });
      
      // delete a ticket
      elt.find('.delete').click(function(){
        $(this).closest('.ticket').find('.price_name select').val('');
      });
      
      // put %%ME%% on a ticket
      elt.find('.me').click(function(){
        var simple_unset = false;
        if ( $(this).val() == $(this).closest('.contact').find('.contact_id input.id').val() )
          simple_unset = true;
        
        // reset previous named ticket to "me"
        $(this).closest('form.named-tickets').find('.contact_id input.id[value="'+$(this).val()+'"]')
          .closest('.contact').find('input').val('').prop('disabled', false);
        
        if ( simple_unset )
          return true;
        
        // reset the current ticket & give it to "me"
        $(this).closest('.contact').find('input:not(.force)').val($(this).prop('title')).prop('disabled',true);
        $(this).closest('.contact').find('.contact_id input.force').val('true');
      });
    }
  });
}

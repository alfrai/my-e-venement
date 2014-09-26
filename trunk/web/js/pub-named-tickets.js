// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  // LI.pubNamedTicketsInitialization(); <-- launched by pub-seated-plan.js
  $('form.named-tickets').submit(function(){
    $.get($(this).prop('action'), $(this).serialize(), LI.pubNamedTicketsData);
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
  
  $.each(json.success.tickets, function(id, ticket){
    var elt = $('form.named-tickets .ticket.sample').clone(true)
      .removeClass('sample')
      .insertBefore($('form.named-tickets .submit'))
    ;
    $.each(['gauge_id', 'seat_id', 'price_id', 'contact_id'], function(key, field){
      elt.attr('data-'+field.replace('_','-'), ticket[field]);
    });
    $.each(['id', 'gauge_name', 'seat_name', 'value', 'contact_id', 'contact_name', 'contact_firstname', 'contact_email', 'comment'], function(key, field){
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
      elt.find('.price_name').remove('> *').prepend($('<select></select>').prop('name', 'price_id'));
      $.each(ticket.prices_list, function(id, name){
        $('<option></option>').val(id).text(name).appendTo(elt.find('.price_name select'));
      });
      elt.find('.price_name select').val(ticket.price_id);
    }
  });
}

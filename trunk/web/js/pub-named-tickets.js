// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  // click on the labels
  $('form.named-tickets label').click(function(){
    $(this).closest('span').find('select, input').first().focus();
  });
  $('form.named-tickets input, form.named-tickets select')
    .change(function(){
      // do not submit the form if a complete contact is not given for $(this)
      if ( $(this).closest('.contact_title, .contact_name, .contact_firstname, .contact_email').length > 0 )
      {
        var go = true;
        var del = $(this).closest('.contact').find('.contact_title select, .contact_name input, .contact_firstname input, .contact_email input').length;
        $(this).closest('.contact').find('.contact_title select, .contact_name input, .contact_firstname input, .contact_email input').each(function(){
          if ( !$.trim($(this).val()) )
          {
            del--;
            go = false;
          }
        });
        if ( !go && del > 0 ) // everything is not filled && everything is not empty
        {
          $(this).closest('.contact').find('.contact_title label, .contact_name label, .contact_firstname label, .contact_email label')
           .css('color', 'red');
          return;
        }
      }
      
      $(this).closest('form').submit();
    })
  ;
  
  $('form.named-tickets').submit(function(){
    if ( location.hash == '#debug' )
    {
      $(this).prop('target', '_blank');
      setTimeout(function(){ $(this).prop('target', null); }, 1000);
      return true;
    }
    
    $.ajax({
      url: $(this).prop('action'),
      type: $(this).prop('method'),
      data: $(this).serialize(),
      success: LI.pubNamedTicketsData,
    });
    $(this).find('.contact').find('.contact_title label, .contact_name label, .contact_firstname label, .contact_email label')
      .css('color', null);
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
  
  if ( json.success.tickets.length == 0 )
    $('#tickets .submit').hide();
  else
    $('#tickets .submit').show();
  
  $.each(json.success.tickets, function(id, ticket){
    var elt = $('form.named-tickets .ticket.sample').clone(true)
      .removeClass('sample')
      .appendTo($('form.named-tickets'))
    ;
    $.each(['gauge_id', 'seat_id', 'price_id', 'contact_id'], function(key, field){
      elt.attr('data-'+field.replace('_','-'), ticket[field]);
    });
    $.each(['id', 'gauge_name', 'seat_name', 'value', 'taxes', 'contact_id', 'contact_title', 'contact_name', 'contact_firstname', 'contact_email', 'comment'], function(key, field){
      if ( elt.find('.'+field+' input, .'+field+' select').length > 0 )
        elt.find('.'+field+' input, .'+field+' select').val(ticket[field]);
      else
        elt.find('.'+field).text(ticket[field]);
    });
    elt.find('input, select').each(function(){
      $(this).attr('name', $(this).attr('name').replace('%%ticket_id%%', ticket.id));
    });
    elt.find('.force').val(ticket['force']);
    
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
      elt.find('.delete').unbind('click').click(function(){
        $(this).closest('.ticket').find('.price_name select').val('');
      });
      
      // put %%ME%% on a ticket
      elt.find('.me').unbind('click').click(function(){
        // reset the current ticket & give it to "me"
        $(this).closest('.contact').find('input:not(.force)').val($(this).prop('title')).prop('disabled',true);
        $(this).closest('.contact').find('.contact_id input.force').val('true');
      });
    }
  });
  
  // playing w/ labels printed over inputs/selects
  $('form.named-tickets input, form.named-tickets select')
    .unbind('focusout').unbind('focus')
    .focusout(function(){
      if ( $.trim($(this).val()) == '' )
      {
        $(this).val('');
        $(this).closest('span').find('label').css('display', '');
      }
    })
    .focus(function(){
      $(this).closest('span').find('label').hide();
    }).focus().delay(1500).focusout() // the delay is needed to let the asynchronous bind finish
  ;
  $('#tickets .submit button').focus();
}

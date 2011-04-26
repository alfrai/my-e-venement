$(document).ready(function(){
  $('.action > a').each(function(){
    $.get($(this).attr('href'),function(data){
      $('#'+$(data).find('form').parent().attr('id')).html($(data).find('form').parent().html());
      ticket_events();
      ticket_prices();
      ticket_print();
    });
  });
});

function ticket_events()
{
  // contact
  $('#contact #autocomplete_transaction_contact_id').keypress(function(e){ if ( e.which == '13' ) $(this).submit(); });
  $('#contact #transaction_professional_id').change(function(){ $(this).submit(); });
  
  ticket_autocomplete(
    '#transaction_contact_id',
    '#autocomplete_transaction_contact_id',
    '/e-venement-2/rp_dev.php/contact/ajax/action');
  if ( $("#autocomplete_transaction_contact_id").length > 0 )
    $('#contact #autocomplete_transaction_contact_id').focus();
  else if ( $('#contact #transaction_professional_id').length > 0 )
    $('#contact #transaction_professional_id').focus();
  if ( $('#contact #transaction_professional_id > option').length < 2 )
  {
    $('#contact #transaction_professional_id').hide();
  }
  
  $('#contact form').unbind().submit(function(){
    $.post($(this).attr('action'),$(this).serialize(),function(data){
      $('#contact').html($(data).find('#contact').html());
      ticket_events();
    });
    return false;
  });
  
  // manifestations
  $('#manifestations form').unbind().submit(function(){
    return false;
  });
  ticket_manif_new_events();

  $('#manifestations .manif_new .toggle_view').unbind().click(function(){
    $('#manifestations .manifestations_add').slideToggle();
  });
  
  $('#manifestations input[name=manif_new]').keypress(function(e){
    if ( e.which == '13' ) {
      $.get($('#manifestations form').attr('action')+'?manif_new='+$(this).val(),function(data){
        // take the list and add it in the GUI
        $('#manifestations .manifestations_add')
          .html($(data).find('#manifestations .manifestations_add').html())
          .slideDown();
        ticket_manif_new_events();
      });
      return false;
    }
  });
  $('.manifestations_list li:first').attr('checked','checked');
  ticket_manif_list_events();
}

function ticket_manif_new_events()
{
  $('.manifestations_add input[type=radio]').click(function(){
    $(this).unbind();
    if ( $('.manifestations_list input[name="'+$(this).attr('name')+'"][value='+$(this).val()+']').length <= 0 )
    {
      $(this).parent().parent().prependTo('.manifestations_list');
      if ( $('#prices .manifestations_list').length > 0 )
        $('#prices .prices_list').fadeIn();
    }
    else
    {
      $(this).parent().parent().remove();
      $('.manifestations_list input[name="'+$(this).attr('name')+'"][value='+$(this).val()+']').attr('selected','selected');
    }
  });
}

function ticket_manif_list_events()
{
  if ( $('.tickets_form > div > a').length > 0 )
  {
    $('.tickets_form > div').load($('.tickets_form > div > a').attr('href')+' .manifestations_list',function(){
      if ( $('.manifestations_list input[type=radio]').length > 0 )
      {
        $('.manifestations_add').slideUp();
        $('#prices .prices_list').fadeIn();
      }
      ticket_transform_hidden_to_span();
    });
  }
}

function ticket_transform_hidden_to_span()
{
  $('.manifestations_list li').each(function(){
    $(this).find('input[type=hidden]').each(function(){
      // adding the spans
      name = $(this).attr('name').replace(/[\[\]]/g,'_');
      price = $(this).attr('name')
        .replace(/^ticket\[prices\]\[\d+\]\[/g,'')
        .replace('][]','');
      if ( $(this).parent().find('.'+name).length > 0 )
        $(this).parent().find('.'+name+' .nb').html(parseInt($(this).parent().find('.'+name+' .nb').html())+1);
      else
        $('<span class="'+name+'" title="'+$(this).attr('title')+'"><span class="nb">1</span> <span class="name">'+price+'</span></span>')
          .appendTo($(this).parent());
    });
  });
  
  // click to remove a ticket
  $('#prices .manifestations_list .prices > span').unbind().click(function(){
    price_name = $(this).find('.name').html();
    $(this).find('.nb').html(parseInt($(this).find('.nb').html())-1);
    $(this).parent().parent().find('input[type=radio]').click();
    $('#prices select[name="ticket[nb]"] option[value=-1]').attr('selected','selected');
    
    // ajax call
    $('#prices input[name="ticket[price_name]"][value='+price_name+']').click();
    $('#prices select[name="ticket[nb]"] option[value=1]').attr('selected','selected');
  });
  
  // total calculation
  ticket_process_amount();
  
  // enabling (or not) payment and validation
  ticket_enable_payment();
}

function ticket_prices()
{
  if ( $('#prices .manifestations_list').length == 0 )
    $('#prices .prices_list').hide();
  
  $('#prices form').unbind().submit(function(){ return false; });
  $('#prices input[type=submit]').unbind().click(function(){
    if ( $('#prices .manifestations_list input:checked').length == 0 )
      return false;
    
    // DB
    $.post($('.tickets_form').attr('action'),$('#prices form').serialize()+'&'+$(this).attr('name')+'='+$(this).val(),function(data){
      $('.sf_admin_flashes').replaceWith($(data).find('.sf_admin_flashes'));
      setTimeout(function(){
        $('.sf_admin_flashes > *').fadeOut();
      },2500);
      
      // add the content
      $('#prices .manifestations_list input:checked').parent().parent().find('.prices')
        .html(
          $(data).find('#prices .manifestations_list input[name="ticket[manifestation_id]"][value='+
            $('#prices .manifestations_list input:checked').val()
          +']')
          .parent().parent().find('.prices').html()
        );
      $('#prices .manifestations_list input:checked').parent().parent().find('.total')
        .html(
          $(data).find('#prices .manifestations_list input[name="ticket[manifestation_id]"][value='+
            $('#prices .manifestations_list input:checked').val()
          +']')
          .parent().parent().find('.total').html()
        );
      
      // transform input hidden into visual tickets
      ticket_transform_hidden_to_span();
    });
    
    return false;
  });
}

function ticket_process_amount()
{
  // the total combinated amount
  total = 0;
  currency = '&nbsp;€'; // default currency
  $('#prices .manifestations_list .manif .total').each(function(){
    if ( $(this).html() )
    {
      total += parseFloat($(this).html().replace(',','.'));
      currency = $(this).html().replace(/^\d+[,\.]\d+/g,'');
    }
  });
  $('#prices .manifestations_list .total .total').html(total.toFixed(2)+currency)
  $('#payment tbody tr.topay .sf_admin_list_td_list_value').html(total.toFixed(2)+currency);
  $('#payment tbody tr.change .sf_admin_list_td_list_value').html((total-parseFloat($('#payment tbody tr.total .sf_admin_list_td_list_value').html())).toFixed(2)+currency);
  
  if ( total <= parseFloat($('#payment tbody tr.total .sf_admin_list_td_list_value').html()) )
  {
    $('#validation').fadeIn();
  }
  else
  {
    $('#validation').fadeOut();
  }
}

function ticket_enable_payment()
{
  // if there are tickets, we fadeIn() needed widgets
  if ( $('#prices .manifestations_list .manif input[type=hidden]').length > 0 )
  {
    $('#print, #payment').fadeIn();

    // if there is nothing left to pay
    if ( parseFloat($('#prices .manifestations_list .total .total').html()) <= 0
      && $('#payment tbody tr').length <= 3 )
    {
      $('#print, #validation').fadeIn();
    }
    // if there is something left to pay
    else if ( parseFloat($('#prices .manifestations_list .total .total').html()) > 0
      || $('#payment tbody tr').length > 3 )
    {
      $('#print, #payment').fadeIn();
    }
  }
  else
    $('#print, #validation, #payment').fadeOut();
}

function ticket_print()
{
  $('#print form').unbind().submit(function(){
    $(document).focus(function(){
      $(this).unbind();
      $('#print input[type=text]').val('');
      $('#print input[type=checkbox]').attr('checked','').change();
      $('#print input[type=submit]').focus();
    });
  });
  $('#print input[type=text]').attr('disabled','disabled');
  $('#print input[type=checkbox]').change(function(){
    if ( $(this).is(':checked') )
    {
      $(this).parent().find('input[type=text]')
        .removeAttr('disabled')
        .focus();
    }
    else
      $('#print input[type=text]').attr('disabled','disabled');
  });
}

function ticket_autocomplete(id,autocomplete,url) {
  $(autocomplete).autocomplete(url, jQuery.extend({}, {
      dataType: 'json',
      parse:    function(data) {
        var parsed = [];
        for (key in data) {
          parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
        }
        return parsed;
      }
    }, { }))
  .result(function(event, data) { jQuery(id).val(data[1]); });
}

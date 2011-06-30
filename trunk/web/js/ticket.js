$(document).ready(function(){
  anchors = $('.action > a').toArray();
  ticket_lauchpad(anchors);
});

function ticket_lauchpad(anchors)
{
  // hiding flashes
  setTimeout(function(){
    $('.sf_admin_flashes').fadeOut();
  },3500);
  
  var a = anchors.shift();
  if ( !a )
  {
    ticket_events();
    ticket_prices();
    ticket_print();
    return true;
  }
  
  $.get($(a).attr('href'),function(data){
    $('#'+$(data).find('form').parent().attr('id')).html($(data).find('form').parent().html());
    return ticket_lauchpad(anchors);
  });
}

function ticket_events()
{
  // contact
  $('#contact #autocomplete_transaction_contact_id').keypress(function(e){ if ( e.which == '13' ) $(this).submit(); });
  $('#contact #transaction_professional_id').change(function(){ $(this).submit(); });
  $('#contact a').unbind().mouseenter(function(){
    $('#contact #micro-show').fadeIn();
    if ( $('#contact #micro-show #sf_fieldset_none').length == 0 )
    {
      $.get($(this).attr('href'),function(data){
        $('#contact #micro-show').find('#sf_fieldset_none, #sf_fieldset_address').remove();
        $(data).find('#sf_fieldset_none, #sf_fieldset_address').appendTo('#contact #micro-show');
        for ( i = 0 ; i < 3 ; i++ )
          $('#contact #micro-show #sf_fieldset_address').find('.sf_admin_form_row:first-child').remove();
      });
    }
  }).mouseleave(function(data){
    $('#contact #micro-show').fadeOut();
  });
  
  
  ticket_autocomplete(
    '#transaction_contact_id',
    '#autocomplete_transaction_contact_id',
    $('#autocomplete_transaction_contact_id').parent().find('a').attr('href'));
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
  
  // delete contact link
  $('#contact .delete-contact').unbind().click(function(){
    $.post($('#contact form').attr('action'),$('#contact form').serialize()+'&delete-contact=yes',function(data){
      $('#contact').html($(data).find('#contact').html());
      ticket_events();
    });
    return false;
  });
  
  // manifestations
  $('#manifestations form').unbind().submit(function(){
    return false;
  });
  ticket_activate_manifs_gauge();
  ticket_manif_new_events();
  
  // toggle link "hide / show"
  $('#manifestations .manif_new .toggle_view').unbind().click(function(){
    $('#manifestations .manifestations_add').slideToggle();
    $('#manifestations .gauge').fadeToggle();
  });
  
  $('#manifestations input[name=manif_new]').keypress(function(e){
    if ( e.which == '13' ) {
      $.get($('#manifestations form').attr('action')+'?manif_new='+$(this).val(),function(data){
        // take the list and add it in the GUI
        $('#manifestations .manifestations_add')
          .html($(data).find('#manifestations .manifestations_add').html())
          .slideDown();
        
        ticket_activate_manifs_gauge();
        ticket_manif_new_events();
      });
      
      return false;
    }
  });
  $('.manifestations_list li:first').attr('checked','checked');
  ticket_manif_list_events();
}

// get the gauge for the list of manifestations
function ticket_activate_manifs_gauge()
{
  $('#manifestations .manifestations_add li span').mouseenter(function(){
    $('#manifestations .manifestations_add li').removeClass('selected');
    $(this).parent().addClass('selected');
    elt = this;
    setTimeout(function(){
      if ( typeof(elt) != 'undefined' )
      if ( $(elt).parent().hasClass('selected') )
        ticket_get_gauge($(elt).find('input[type=radio]').val(),$('#manifestations .gauge'));
      elt = null;
    },300);
  });
  
  // if reclicking on a gauge, it refreshes it from DB
  $('#manifestations .gauge').unbind().click(function(){
    if ( $(this).find('input[name=gauge-id]').length > 0 )
      ticket_get_gauge($(this).find('input[name=gauge-id]').val(), $('#manifestations .gauge'), true);
  });
}
function _ticket_activate_manifs_gauge(elt)
{
}

// get the gauge for the selected manifestations (w/ tickets)
function ticket_activate_prices_gauge()
{
  // when switching from manifestation, updating the gauge
  $('#prices .manifestations_list input[type=radio]').click(function(){
    if ( $(this).is(':checked') )
      ticket_get_gauge($(this).val(),$('#prices .gauge'));
  });
  
  $('#prices .gauge').css('height','232px');
  $('#prices .tickets_form').addClass('full');
  
  ticket_get_gauge($('#prices .manifestations_list input:checked').val(),$('#prices .gauge'));
  
  // if reclicking on a gauge, it refreshes it from DB
  $('#prices .gauge').unbind().click(function(){
    if ( $(this).find('input[name=gauge-id]').length > 0 )
      ticket_get_gauge($(this).find('input[name=gauge-id]').val(), $('#prices .gauge'), true);
  });
}
function ticket_gauge_backup()
{
  $('.gauge > div').appendTo('#manifestations');
  $('#manifestations .gauge-id').hide();
}
function ticket_get_gauge(manif_id, gauge_elt, force)
{
  // restore
  if ( !force && $('#gauge-'+manif_id).length == 1 )
  {
    // backup
    ticket_gauge_backup();
    // replace
    gauge_elt.html($('#gauge-'+manif_id).html());
  }
  // get from DB
  else
  {
    $('#manifestations #gauge-'+manif_id).remove();
    $.get($('#gauge_url').attr('href')+'?id='+manif_id,function(data){
      gauge_elt.html($(data).find('.gauge').html());
      if ( gauge_elt.find('.free .nb').html() < 0 )
      {
        $('.manifestations_list input[name="ticket[manifestation_id]"][value='+manif_id+']')
          .parent().addClass('alert');
      }
    });
  }
}

function ticket_manif_new_events()
{
  $('.manifestations_add input[type=radio]').click(function(){
    $(this).unbind();
    if ( $('.manifestations_list input[name="'+$(this).attr('name')+'"][value='+$(this).val()+']').length <= 0 )
    {
      $(this).parent().parent().find('span').unbind();
      ticket_gauge_backup();
      $(this).parent().parent().prependTo('.manifestations_list');
      if ( $('#prices .manifestations_list').length > 0 )
      {
        ticket_activate_prices_gauge();
        $('#prices .prices_list').fadeIn();
      }
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
        $('#manifestations .gauge').fadeOut();
        $('#prices .prices_list').fadeIn();
        ticket_activate_prices_gauge();
      }
      ticket_transform_hidden_to_span(true);
    });
  }
}

function ticket_transform_hidden_to_span(all)
{
  if ( typeof(all) == 'undefined' ) all = false;
  
  $('.manifestations_list li [type=radio]'+(all ? '' : ':checked')).parent().parent().find('.prices span').remove();
  $('.manifestations_list li [type=radio]'+(all ? '' : ':checked')).each(function(){
    $(this).parent().parent().find('input[type=hidden]').each(function(){
      // adding the spans
      name = $(this).attr('name').replace(/[\[\]]/g,'_').replace(/__/g,'_').replace(/_+$/,'').replace(' ','_');
      price = $(this).attr('name')
        .replace(/^ticket\[prices\]\[\d+\]\[/g,'')
        .replace('][]','');
      
      if ( $(this).parent().find('.'+name).length > 0 )
        $(this).parent().find('.'+name+' .nb').html(parseInt($(this).parent().find('.'+name+' .nb').html())+1);
      else
        $('<span class="'+name+' ticket_prices" title="'+$(this).attr('title')+'"><span class="nb">1</span> <span class="name">'+price+'</span><span class="tickets_id"></span><span class="value">'+$(this).val()+'</span></span>')
          .appendTo($(this).parent());
      $(this).parent().find('.'+name+' .tickets_id').append($(this).attr('alt')+'<br/>');
    });
  });
  
  // click to remove a ticket
  $('#prices .manifestations_list .prices > span').unbind().click(function(){
    $('#prices [name=select_all]').attr('checked',false);
    price_name = $(this).find('.name').html();
    selected = $('#prices [name="ticket[nb]"]').val();
    $(this).find('.nb').html(parseInt($(this).find('.nb').html())-selected);
    $(this).parent().parent().find('input[type=radio]').click();
    $('#prices [name="ticket[nb]"]').val(-selected);
    
    // ajax call
    $('#prices input[name="ticket[price_name]"][value='+price_name+']').click();
    $('#prices [name="ticket[nb]"]').val(selected);
    
    ticket_gauge_update_click(this);
  });
  
  // total calculation
  ticket_process_amount();
  
  // enabling (or not) payment and validation
  ticket_enable_payment();
}

// gauge updates when clicking on a price or a ticket
function ticket_gauge_update_click(elt)
{
  if ( elt )
    $(elt).parent().parent().find('input[type=radio]').attr('selected','selected');
  $('#prices .gauge').click();
}

function ticket_prices()
{
  if ( $('#prices .manifestations_list').length == 0 )
    $('#prices .prices_list').hide();
  
  $('#prices form').unbind().submit(function(){ return false; });
  
  // clicking on a price ... adding a ticket
  $('#prices input[type=submit]').unbind().click(function(){
    
    if ( $('#prices .manifestations_list input:checked').length == 0 )
      return false;
    
    // DB
    elt = $(this);
    $.post($('.tickets_form').attr('action'),$('#prices form').serialize()+'&'+$(this).attr('name')+'='+$(this).val(),function(data){
      if ( $.trim($(data).find('.sf_admin_flashes').html()) != '' )
      {
        $('.sf_admin_flashes').replaceWith($(data).find('.sf_admin_flashes'));
        setTimeout(function(){
          $('.sf_admin_flashes > *').fadeOut();
        },2500);
      }
      
      // the gauge
      ticket_gauge_update_click();
      
      // add the content
      //alert($('#prices .manifestations_list input:checked').val());
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
      
      // the other line
      if ( $('#prices .prices_list [name="ticket[nb]"]').val() > 0
        && $('#prices .prices_list [name="select_all"]:checked').length > 0
        && typeof($('#prices .manifestations_list input:checked').parent().parent().next().find('[type=radio]').val()) != 'undefined' )
      {
        $('#prices .manifestations_list input:checked').parent().parent().next().find('[type=radio]').attr('checked',true);
        $(elt).click();
      }
    });
    
    return false;
  });
}

function ticket_process_amount(add)
{
  if ( add == 'undefined' ) add = false;
  
  // the total combinated amount
  total = 0;
  currency = '&nbsp;€'; // default currency
  $('#to_pay, #prices .manifestations_list .manif .total').each(function(){
    if ( $(this).html() )
    {
      total += parseFloat($(this).html().replace(',','.').replace('&nbsp;',''));
      currency = $(this).html().replace(/^-{0,1}(\d+&nbsp;)*\d+[,\.]\d+/g,'');
    }
  });
  $('#prices .manifestations_list .total .total').html(total.toFixed(2)+currency);
  $('#payment tbody tr.topay .sf_admin_list_td_list_value').html(total.toFixed(2)+currency);
  $('#payment tbody tr.change .sf_admin_list_td_list_value').html(
    (total-parseFloat($('#payment tbody tr.total .sf_admin_list_td_list_value').html().replace(',','.').replace('&nbsp;',''))).toFixed(2)
    +currency);
  
  if ( total <= parseFloat($('#payment tbody tr.total .sf_admin_list_td_list_value').html().replace(',','.').replace('&nbsp;','')) )
  {
    $('#validation').fadeIn();
    if ( add )
      $('#print form.print').submit();
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
    $(this).append('<input type="hidden" name="manifestation_id" value="'+$('.manifestations_list input[type=radio]:checked').val()+'" id="manifestation_id" />');
    $(document).focus(function(){
      $(this).unbind();
      $('#print input[type=text]').val('');
      $('#print input[type=checkbox]').attr('checked','').change();
      $('#print input[type=submit]:first').focus();
      $('#print #manifestation_id').remove();
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
  
  $('#print input[name=cancel-order]').click(function(){
    $.get($('#print form.accounting').attr('action')+'?cancel-order',function(data){
      $('#print input[name=cancel-order]').fadeOut();
      $('#prices .gauge').click();
    });
    return false;
  });
  $('#print input[name=order]').click(function(){
    $('#print input[name=cancel-order]').fadeIn();
    setTimeout(function(){$('#prices .gauge').click();},4000);
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


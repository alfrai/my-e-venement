$(document).ready(function(){
  anchors = $('.action > a').toArray();
  ticket_lauchpad(anchors);
  if ( $('#transaction-id').length > 0 )
  {
    window.onbeforeunload = function(){
      return false;
    };
    $('#validation form').submit(function(){
      window.onbeforeunload = null;
    });
  }
  
  $('link[rel=stylesheet][media=screen]').attr('media','all');
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
    $('#'+$($.parseHTML(data)).find('form').parent().attr('id')).html($($.parseHTML(data)).find('form').parent().html());
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
        $($.parseHTML(data)).find('#sf_fieldset_none, #sf_fieldset_address').appendTo('#contact #micro-show');
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
      $('#contact').html($($.parseHTML(data)).find('#contact').html());
      ticket_events();
    });
    return false;
  });
  
  // delete contact link
  $('#contact .delete-contact').unbind().click(function(){
    $.post($('#contact form').attr('action'),$('#contact form').serialize()+'&delete-contact=yes',function(data){
      $('#contact').html($($.parseHTML(data)).find('#contact').html());
      ticket_events();
    });
    return false;
  });
  
  // add contact link
  $('#contact .create-contact').unbind().click(function(){
    var w = window.open($(this).attr('href')+'&name='+$('#contact #autocomplete_transaction_contact_id').val(),'new_contact');
    w.onload = function(){
      setTimeout(function(){
        $(w.document).ready(function(){
          $(w.document).find('.sf_admin_actions_form .sf_admin_action_list, .sf_admin_actions_form .sf_admin_action_save_and_add')
            .remove();
        });
        w.onunload = function(){
          setTimeout(function(){
            $(w.document).ready(function(){
              if ( contact_id = $(w.document).find('[name="contact[id]"]').val() )
              {
                $('#contact form [name="transaction[contact_id]"]').val(contact_id);
                $('#contact form').submit();
              }
              w.close();
            });
          },2500);
        };
      },2500);
    };
    return false;
  });
  
  
  // manifestations
  $('#manifestations form').unbind().submit(function(){
    manifs = $('#manifestations form [name=manif_new]').val().replace(/#/g,'%23');
    $.get($('#manifestations form').attr('action')+'?manif_new='+manifs,function(data){
      // take the list and add it in the GUI
      $('#manifestations .manifestations_add')
        .html($($.parseHTML(data)).find('#manifestations .manifestations_add').html())
        .slideDown();
      ticket_activate_manifs_gauge();
      ticket_manif_new_events();
      if ( $('#manifestations form [name=manif_new]').val().substring(0,7) == '#manif-' )
      {
        setTimeout(function(){
          $('#manifestations .manifestations_add [name="ticket[manifestation_id]"]').click();
          $('.manifestations_list [name="ticket[manifestation_id]"]').click();
          $('#manifestations .manifestations_add').slideUp();
          $('#manifestations .gauge').fadeOut();
        },500);
      }
    });
    return false;
  });
  ticket_activate_manifs_gauge();
  ticket_manif_new_events();
  
  // toggle link "hide / show"
  $('#manifestations .manif_new .toggle_view').unbind().click(function(){
    $('#manifestations .manifestations_add').slideToggle();
    $('#manifestations .gauge').fadeToggle();
  });
  
  $('.manifestations_list li:first').attr('checked','checked');
  
  // auto-select a manifestation with a specific anchor ref in URL
  if ( document.location.hash.substring(1,7) == 'manif-' )
  {
    $('#manifestations form [name=manif_new]').val(document.location.hash);
    $('#manifestations form').submit();
  }
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

// get the gauge for the selected manifestations (w/ tickets)
function ticket_activate_prices_gauge()
{
  $('#prices .gauge').css('height','232px');
  $('#prices .tickets_form').addClass('full');
  ticket_get_gauge($('#prices .manifestations_list input:checked').val(),$('#prices .gauge'));
  
  // update prices display
  ticket_display_prices();
  $('.manifestations_list .workspaces').click(function(){
    $(this).parent().find('input[type=radio]').click();
  });
  $('.manifestations_list .workspaces').change(ticket_display_prices);
  
  // when switching from manifestation, updating the gauge
  $('#prices .manifestations_list input[type=radio]').unbind().click(function(){
    if ( $(this).is(':checked') )
    {
      ticket_get_gauge($(this).val(),$('#prices .gauge'));
      // update prices display
      ticket_display_prices();
    }
  });
  
  // if reclicking on a gauge, it refreshes it from DB
  $('#prices .gauge').unbind().click(function(){
    if ( $(this).find('input[name=gauge-id]').length > 0 )
      ticket_get_gauge($(this).find('input[name=gauge-id]').val(), $('#prices .gauge'), true);
  });
}
function ticket_gauge_backup(gauge_elt)
{
  // remove old 
  eltid = gauge_elt.find('.backup').attr('id');
  $('#manifestations > #'+eltid).remove();
  
  // backup new
  gauge_elt.find('.backup').clone(true).appendTo('#manifestations');
}
function ticket_get_gauge(manif_id, gauge_elt, force)
{
  // get from DB
  if ( force || $('#gauge-'+manif_id).length == 0 )
  {
    $.get($('#gauge_url').attr('href')+'?id='+manif_id,function(data){
      // display
      gauge_elt.html($($.parseHTML(data)).find('.gauge').html());
      
      // alert
      if ( gauge_elt.find('.free .nb').html() < 0 )
      {
        $('.manifestations_list input[name="ticket[manifestation_id]"][value='+manif_id+'], #manifestations input[name="ticket[manifestation_id]"][value='+manif_id+']')
          .parent().addClass('alert');

        if ( $('#manifestations #force-alert').length > 0
          && $('.manifestations_list input[name="ticket[manifestation_id]"][value='+manif_id+']').parent().hasClass('alert') )
        {
          $('#gauge-alert').fadeIn().html(($('#manifestations #force-alert').html()));
          setTimeout(function(){ $('#gauge-alert').fadeOut() },2000);
        }
      }
      
      // backup
      ticket_gauge_backup(gauge_elt);
    });
  }
  
  // restore backup
  gauge_elt.html($('#gauge-'+manif_id));
}

function ticket_manif_new_events()
{
  $('.manifestations_add input[type=radio]').unbind().click(function(){
    $(this).unbind();
    if ( $('.manifestations_list input[name="'+$(this).attr('name')+'"][value='+$(this).val()+']').length <= 0 )
    {
      $(this).parent().parent().find('span').unbind();
      ticket_gauge_backup($('.manifestations_add .gauge'));
      $(this).parent().parent().prependTo('.manifestations_list ul');
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
        ticket_activate_prices_gauge();
        $('#prices .prices_list').fadeIn().css('opacity','1');
      }
      ticket_transform_hidden_to_span(true);
    });
  }
}

function ticket_transform_hidden_to_span(all)
{
  if ( typeof(all) == 'undefined' ) all = false;
  
  // action to update the workspace gauge
  $('.manifestations_list li [type=radio]'+(all ? '' : ':checked')).parent().parent().find('.prices .workspace').unbind().click(function(){
    ticket_get_ws_gauge($(this).find('.url').html());
  }).click();
  
  // visual tickets
  $('.manifestations_list li [type=radio]'+(all ? '' : ':checked')).parent().parent().find('.prices .ticket_prices').remove();
  $('.manifestations_list li [type=radio]'+(all ? '' : ':checked')).each(function(){
    $(this).parent().parent().find('.prices input[type=hidden]').each(function(){
      // adding the spans
      name = $(this).attr('name').replace(/[\[\]]/g,'_').replace(/__/g,'_').replace(/_+$/,'').replace(/ /g,'_');
      price = $(this).attr('name')
        .replace(/^ticket\[prices\]\[\d+\]\[/g,'')
        .replace('][]','');
      if ( $(this).parent().find('.'+name+'.'+$(this).attr('class')).length > 0 )
      {
        $(this).parent().find('.'+name+'.'+$(this).attr('class')+' input[type=text].nb').val(parseInt($(this).parent().find('.'+name+'.'+$(this).attr('class')+' input[type=text].nb').val())+1);
        $(this).parent().find('.'+name+'.'+$(this).attr('class')+' input[type=hidden].nb').val(parseInt($(this).parent().find('.'+name+'.'+$(this).attr('class')+' input[type=hidden].nb').val())+1);
      }
      else
        $('<span class="'+name+' ticket_prices '+$(this).attr('class')+'" title="'+$(this).attr('title')+'"><input type="text" class="nb" name="hidden_nb" value="1" autocomplete="off" maxlength="3" /><input type="hidden" class="nb" name="hidden_nb" value="1"> <span class="name">'+price+'</span><span class="tickets_id"></span><span class="value">'+$(this).val()+'</span></span>')
          .appendTo($(this).parent());
      $(this).parent().find('.'+name+'.'+$(this).attr('class')+' .tickets_id').append($(this).attr('alt')+'<br/>');
    });
  });
  
  $('#prices .manifestations_list .prices .ticket_prices.notprinted input.nb, #prices .manifestations_list .prices .ticket_prices.integrated input.nb').unbind().change(function(){
    nb = $(this).parent().find('input[type=text].nb').val() - $(this).parent().find('input[type=hidden].nb').val();
    orig = $('#prices input[name="ticket[nb]"]').val();
    
    $('#prices input[name="ticket[nb]"]').val(nb);
    $('#prices input[name="ticket[price_name]"][value="'+$(this).parent().find('.name').html()+'"]').click();
    $('#prices input[name="ticket[nb]"]').val(orig);
  }).keypress(function(e){ if ( e.which == '13' ) {
    // when changing quantities arbitrary through the input text
    $(this).change();
    return false;
  }});
  
  // click to remove a ticket
  $('#prices .manifestations_list .prices .ticket_prices.notprinted .name, #prices .manifestations_list .prices .ticket_prices.integrated .name').unbind().click(function(){
    $('#prices .prices_list').removeClass('cancel');
    gid = $(this).parent().parent().attr('class').replace(/.* gauge-(\d+).*/g,'$1');
    $(this).parent().parent().parent().parent().find('.workspaces [name="ticket[gauge_id]"]').val(gid);
    $('#prices [name=select_all]').attr('checked',false);
    price_name = $(this).html();
    selected = $('#prices [name="ticket[nb]"]').val();
    $(this).parent().find('.nb').val(parseInt($(this).parent().find('.nb').val())-selected);
    $(this).parent().parent().parent().parent().find('input[type=radio]').click();
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

function ticket_display_prices()
{
  prices = JSON.parse($(search = '#ts-tickets [name="ticket[manifestation_id]"]:checked, #ts-manifestations [name="ticket[manifestation_id]"]:checked, .manifestations_list [name="ticket[manifestation_id]"]:checked').closest('li').find('.manif_prices_list').html());
  elts = $('.tickets_form .prices_list [name="ticket[price_name]"]').hide().removeClass('show');
  for ( var id in prices )
  {
    elts.each(function(){
      if ( $(this).val() == prices[id]['price'] )
      if ( prices[id]['gauges'][$(search).closest('li').find('[name="ticket[gauge_id]"]').val()] != undefined )
        $(this).show().addClass('show');
    });
  }
}

function ticket_get_ws_gauge(json_url)
{
  if ( json_url == null )
    return;
  
  $.getJSON(json_url+'?json',function(data){
    url = $('.manifestations_list .workspace.gauge-'+data.id+' .ws-gauge .url');
    $('.manifestations_list .workspace.gauge-'+data.id+' .ws-gauge span').remove();
    $('.manifestations_list .workspace.gauge-'+data.id+' .ws-gauge')
      .append(url)
      .append('<span class="printed" style="width: '+(parseInt(data.total) == 0 ? '0' : data.booked.printed*100/(parseInt(data.total)+(parseInt(data.free) < 0 ? -parseInt(data.free) : 0)))+'%" title="'+data.booked.printed+'">&nbsp;</span>')
      .append('<span class="ordered" style="width: '+(parseInt(data.total) == 0 ? '0' : data.booked.ordered*100/(parseInt(data.total)+(parseInt(data.free) < 0 ? -parseInt(data.free) : 0)))+'%" title="'+data.booked.ordered+'">&nbsp;</span>')
      .append('<span class="asked" style="width: '+(parseInt(data.total) == 0 ? '0' : data.booked.asked*100/(parseInt(data.total)+(parseInt(data.free) < 0 ? -parseInt(data.free) : 0)))+'%" title="'+data.booked.asked+'">&nbsp;</span>')
      .append('<span class="free" style="width: '+(parseInt(data.total) == 0 ? '0' : (parseInt(data.free) < 0 ? 0 : parseInt(data.free))*100/(parseInt(data.total)+(parseInt(data.free) < 0 ? -parseInt(data.free) : 0)))+'%" title="'+parseInt(data.free)+'">&nbsp;</span>');
    $('.manifestations_list .workspace.gauge-'+data.id+' .ws-name').attr('title',parseInt(data.total));
    
    if ( parseInt(data.free) <= 0 )
    {
      $('.manifestations_list .workspace.gauge-'+data.id).addClass('alert');
      if ( $('#manifestations #force-alert').length > 0 )
      {
        $('#gauge-alert').fadeIn().html(($('#manifestations #force-alert').html()));
        setTimeout(function(){ $('#gauge-alert').fadeOut() },2000);
      }
      $('.manifestations_list select[name="ticket[gauge_id]"] option[value='+data.id+']').addClass('alert').parent().addClass('alert');
    }
  });
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
    
    if ( $('#prices .manifestations_list input[type=radio]:checked').length == 0 )
      return false;
    
    // cancelling tickets
    if ( $(this).closest('.prices_list.cancel').length > 0 )
    {
      mid = $('#prices .manifestations_list input[name="ticket[manifestation_id]"]:checked').val();
      price_name = $(this).val();
      qty = $('#prices .prices_list input[name="ticket[nb]"]').val();
      tid = $('#global_transaction_id').html();
      
      url = $('#prices .prices_list a.cancel').attr('href');
      
      window.open(url+'?qty='+qty+'&manifestation_id='+mid+'&price_name='+price_name+'&id='+tid);
      
      $(this).closest('.prices_list.cancel').removeClass('cancel');
      return false;
    }
    
    // DB
    elt = $(this);
    manif_id = $('#prices form input[name="ticket[manifestation_id]"]:checked').val();
    form = $('#prices form').clone(true);
    form.find('input[name="ticket[manifestation_id]"][value='+manif_id+']').attr('checked','checked');
    form.find('.prices .workspace input[type=hidden]').remove();
    form.find('input[name="ticket[gauge_id]"]').remove();
    serialized = form.serialize()+'&'+encodeURIComponent('ticket[gauge_id]')+'='+$('#prices .manifestations_list input[type=radio]:checked').closest('li').find('[name="ticket[gauge_id]"]').val()+'&'+$(this).attr('name')+'='+encodeURIComponent($(this).val());
    $.post($('.tickets_form').attr('action'),serialized,function(data){
      if ( $.trim($($.parseHTML(data)).find('.sf_admin_flashes').html()) != '' )
      {
        $('.sf_admin_flashes').replaceWith($($.parseHTML(data)).find('.sf_admin_flashes'));
        setTimeout(function(){
          $('.sf_admin_flashes > *').fadeOut();
        },2500);
      }
      
      // if it is a seating plan which is displaid
      if ( $($.parseHTML(data)).find('#seating-plan').length > 0 )
      {
        // appearing
        $('#transition').show();
        $('#seating-plan').remove();
        $('#prices').prepend($($.parseHTML(data)).find('#seating-plan'));
        $('#seating-plan input:first').focus();
        $('#seating-plan .reset').click(function(){
          $('#transition').hide();
          $('#seating-plan').remove();
          ticket_transform_hidden_to_span();
          $('#prices form [name="ticket[numerotation]"]').val('');
        });
        
        // submitting
        $('#seating-plan').submit(function(){
          // preparing the form
          $('#prices form input[name="ticket[numerotation]"]').val($('#seating-plan [name=numerotation]').val());
          nb = $('#prices form input[name="ticket[nb]"]').val();
          $('#prices form input[name="ticket[nb]"]').val($('#seating-plan [name=nb]').val());
          
          // submitting
          $('#prices input[name="ticket[price_name]"][value="'+$(this).find('[name=price_name]').val()+'"]').click();
          
          // get back to initial state
          $('#prices form input[name="ticket[nb]"]').val(nb);
          $('#seating-plan .reset').click();
          return false;
        });
        return true;
      }
      
      // the gauge
      ticket_gauge_update_click();
      
      // add the content
      $('#prices .manifestations_list input:checked').parent().parent().find('.prices')
        .html(
          $($.parseHTML(data)).find('#prices .manifestations_list input[name="ticket[manifestation_id]"][value='+
            $('#prices .manifestations_list input:checked').val()
          +']')
          .parent().parent().find('.prices').html()
        );
      $('#prices .manifestations_list input[type=radio]:checked').parent().parent().find('.total')
        .html(
          $($.parseHTML(data)).find('#prices .manifestations_list input[name="ticket[manifestation_id]"][value='+
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
    $('#validation .form-valid').fadeIn();
    if ( add )
      $('#print form.print').submit();
  }
  else
  {
    $('#validation .form-valid').fadeOut();
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
      $('#print, #validation form').fadeIn();
    }
    // if there is something left to pay
    else if ( parseFloat($('#prices .manifestations_list .total .total').html()) > 0
      || $('#payment tbody tr').length > 3 )
    {
      $('#print, #payment').fadeIn();
    }
  }
  else
  {
    if ( $('#payment input[name="ids[]"]').length == 0 )
      $('#payment').fadeOut();
    $('#print, #validation .form-valid').fadeOut();
  }
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
  $('#print input[name="duplicate"]').change(function(){
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
    }, { max: 25 }))
  .result(function(event, data) { jQuery(id).val(data[1]); });
}


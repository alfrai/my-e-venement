// the global var that can be used everywhere as a "root"
if ( li == undefined )
  var li = {};

$(document).ready(function(){
  // AUTO VALIDATIONS
  $('[name="transaction[contact_id]"]'+
  ', [name="transaction[professional_id]"]'+
  ', [name="transaction[description]"]')
    .change(function(){ $(this).closest('form').submit(); });
  
  li.initContent();
  $('#sf_admin_content form:not(.noajax)').submit(li.formSubmit);
  
  // PLAYING W/ CART'S CONTENT
  // sliding content
  $('#li_transaction_field_content h2').click(function(){
    $(this).closest('.bunch').find('.families').slideToggle(function(){
      if ( !$(this).is(':hidden') )
        return;
      $(this).find('.ui-state-highlight').focusout();
    });
  });
  $('#li_transaction_field_content h3').click(function(){
    $(this).closest('.family').find('.items').each(function(){
      var showing = $(this).is(':hidden');
      
      $(this).slideToggle();
      if ( !showing ) $(this).find('.ui-state-highlight').focusout();
    });
  });
  
  // retrieve focusout()s
  $('.highlight input, .highlight select, .highlight textarea')
    .focusout(function(){ return false; })
    .focusin (function(){ $(this).closest('.highlight').focusin(); return false; });
  
  // changing quantities
  $('#li_transaction_field_content .qty a').click(function(){ var input = $(this).closest('.qty').find('input'); input.val(parseInt(input.val(),10)+($(this).is(':first-child') ? -1 : 1)).change(); });
  $('#li_transaction_field_content .qty input').focusout(function(){ return false; }).select(function(){
    $(this).prop('defaultValue',$(this).val()); // quite useless... but whatever
  }).change(function(){
    if ( $(this).closest('.highlight.ui-state-highlight').length == 0 )
      $(this).closest('.highlight').focusin();
    if ( isNaN(parseInt($(this).val(),10)) )
      $(this).val($(this).prop('defaultValue'));
    
    if ( $(this).prop('defaultValue') !== $(this).val() )
    {
      var diff = $(this).val() - $(this).prop('defaultValue');
      $(this).select();
      
      var form = $('#li_transaction_field_price_new form');
      var orig = form.find('[name="transaction[price_new][qty]"]').val();
      
      // set values & subit
      form.find('[name="transaction[price_new][qty]"]').val(diff);
      form.find('[name="transaction[price_new][price_id]"]').val($(this).closest('.declination').attr('data-price-id'));
      form.find('[name="transaction[price_new][gauge_id]"]').val($(this).closest('.item').attr('data-gauge-id'));
      form.submit();
      
      // reinit
      form.find('[name="transaction[price_new][qty]"]').val(orig);
    }
  });
  
  // total calculation
  $('#li_transaction_field_content .item .total').select(li.calculateTotals).select();
  
  // showing numerotation & ids
  $('#li_transaction_field_content .item .price_name').mousedown(function(){
    $(this).closest('.declination').find('.ids').addClass('show');
    $(this).closest('.highlight').focusin();
  });
  $(document).mouseup(function(){
    $('#li_transaction_field_content .ids').removeClass('show');
  });
  
  // showing the gauges
  $('#li_transaction_field_content .item').focusin(function(){
    $('#li_transaction_field_product_infos *').remove(); // cleaning products infos
    
    if ( $(this).find('.data .gauge.raw').length > 0 )
    {
      if ( !$(this).find('.data .gauge.raw').html() )
      {
        var gauge = this;
        $.get($(this).find('.data .gauge.raw').prop('href'), function(data){
          $(gauge).find('.data .gauge.raw').html(JSON.stringify(data));
          li.renderGauge(gauge);
        });
      }
      else
      {
        li.renderGauge(this);
      }
    }
  }).dblclick(function(){
    $(this).find('.data .gauge.raw').html('');
    $(this).find('.data .gauge.seated.picture').remove();
    $(this).focusin();
  });
  
  // refreshing the gauges if the document has lost focus
  $(window).blur(function(){
    $('#li_transaction_field_product_infos *').remove(); // cleaning products infos
    $('#li_transaction_field_content .item .data .gauge.raw').html('');
    $('#li_transaction_field_content .item .data .gauge.seated.picture').remove();
  });
  
  // CONTACT CHANGE & INIT
  $.each([
    '#li_transaction_field_contact_id',
    '#li_transaction_field_professional_id'
  ], function(index, value){ li.initTouchscreen(value); });
  
  // CONTACT CREATION
  $('#li_transaction_field_contact_id .create-contact').click(function(){
    var w = window.open($(this).prop('href')+'&name='+$('#li_transaction_field_contact_id [name="autocomplete_transaction[contact_id]"]').val(),'new_contact');
    
    w.onload = function(){
      setTimeout(function(){
        $(w.document).ready(function(){
          $(w.document).find('.sf_admin_actions_form .sf_admin_action_list, .sf_admin_actions_form .sf_admin_action_save_and_add')
            .remove();
        });
        w.onunload = li.goBackToTransaction;
      },2500);
    };
    
    return false;
  });
  
  // CLICK WIDGETS
  $('#sf_admin_content .highlight').focusout(function(){
    $(this).removeClass('ui-state-highlight'); // removing any highlight 
    return false; // to avoid the event to go up in the JS tree
  }).focusin(function(){
    $('#sf_admin_content .ui-state-highlight').focusout();
    $(this).addClass('ui-state-highlight');
    
    if ( $(this).hasClass('board-alpha') || $(this).closest('.board-alpha').length > 0 )
      $('#li_transaction_field_board').addClass('alpha');
    if ( !$(this).hasClass('board-alpha') || $(this).closest('.board-alpha').length == 0 )
      $('#li_transaction_field_board').removeClass('alpha');
    $('#li_transaction_field_board button').each(function(){
      if ( $(this).find('.num, .alpha').length > 0 )
        $(this).val($(this).closest('#li_transaction_field_board').hasClass('num') ? $(this).find('.num').html() : $(this).find('.alpha').html());
    });
    
    if ( !$(this).is('#li_transaction_field_professional_id, #li_transaction_field_contact_id') )
      $('#li_transaction_field_informations .vcard').slideUp();
    
    return false; // to avoid the event to go up in the JS tree
  }).click(function(){
    $(this).focusin();
  });
  
  // vCard & co
  $('#li_transaction_field_professional_id, #li_transaction_field_contact_id').click(function(){
    $('#li_transaction_field_professional_id').addClass('ui-state-highlight');
    $('#li_transaction_field_contact_id').addClass('ui-state-highlight');
    if ( $('#li_transaction_field_contact_id .data a').length > 0
      && $('#li_transaction_field_informations .vcard').length == 0 )
    {
      $.get($('#li_transaction_field_contact_id .data a').prop('href')+'/vcf', function(data){
        vcard = vCard.initialize(data);
        data = $.parseHTML(vcard.to_html());
        
        $(data).find('.type').remove();
        $(data).find('.postal-code').each(function(){ $(this).insertBefore($(this).closest('address').find('.locality')); });
        $('#li_transaction_field_informations').prepend($(data));
      });
    }
    else
      $('#li_transaction_field_informations .vcard').slideDown('slow');
  });
  
  // THE BOARD
  $('#li_transaction_field_board button').click(li.boardClick)
  
  // ARROWS ON DOCUMENT / ON ITEMS
  $(document).keydown(function(e){
    if ( $('#li_transaction_field_content .item.ui-state-highlight').length == 0
      || $('#li_transaction_field_content .item').length == 1 )
      return true;
    
    if ( e.which != 38 && e.which != 40 )
      return true;
    
    var items = $('#li_transaction_field_content .families:not(.sample) .family:not(.total) .item:not(.total)').toArray();
    switch ( e.which ) {
    case 40: // arrow down
      for ( i = 0 ; i < items.length ; i++ )
      {
        if ( $(items[i]).is('.ui-state-highlight') )
        {
          $(items[++i]).focusin();
          break;
        }
      }
      break;
    case 38: // arrow up
      for ( i = 0 ; i < items.length ; i++ )
      {
        if ( $(items[i]).is('.ui-state-highlight') )
        {
          $(items[--i]).focusin();
          break;
        }
      }
      break;
    }
    
    return false;
  });
  
  // FLASHES
  // hide the flashes after a while
  setTimeout(
    function(){ $('.sf_admin_flashes > *').fadeOut('slow',function(){ $(this).remove(); }); }
    , 2500
  );
  
  // DISPLAYS A WARNING MESSAGE WHEN THE WINDOW ATTEMPS TO BE CLOSED
  $(window).on('beforeunload', li.closeTransaction);
  
  // RESPONSIVE DESIGN
  li.responsiveDesign();
  
  // NEW PAYMENT
  $('#li_transaction_field_payment_new [name="transaction[payment_new][payment_method_id]"]').each(function(){
    $(this).closest('li').find('input, label').hide();
    $('<button />').text($(this).closest('li').find('label').text())
      .click(function(){
        $(this).closest('li').find('input').prop('checked',true);
      })
      .appendTo($(this).closest('li'));
  });
  $('#li_transaction_field_payment_new .submit').hide();
});


li.checkGauges = function(form){
  var qty = 0;
  var go = true;
  
  $('#li_transaction_field_content #li_transaction_manifestations .families:not(.sample) .item').each(function(){
    if ( go == false )
      return;
    
    if ( $(this).find('tbody .declination [name="qty"]').length > 0 )
    {
      var gauge = this;
      qty++;
      $.get($(this).find('.data .gauge.raw').prop('href'), function(data){
        var elts = $(gauge).find('tbody .declination:not(.printed) [name="qty"]');
        $(gauge).find('.data .gauge.raw').html(JSON.stringify(data));
        li.renderGauge(gauge, true);
        
        // overbooking
        var total = 0;
        
        // a loophole for the tickets of the current transaction
        if ( $('#li_transaction_field_payments_list [name="cancel-order"]').css('visibility') == 'hidden' )
        elts.each(function(){
          total += parseInt($(this).val(),10);
        });
        
        if ( data.free - total < 0 )
        {
          go = false;
          elts.addClass('blink');
          li.blinkQuantities(elts, true);
        }
        
        qty--;
        if ( qty == 0 )
        {
          var type = $('#li_transaction_field_close .overbooking .type').attr('data-type');
          if ( go == false && type == 'block' )
          {
            // if the user cannot overbook, give him an alert
            li.alert($('#li_transaction_field_close .overbooking .msg.'+type).html());
          }
          else if ( go || confirm($('#li_transaction_field_close .overbooking .msg.warn').text()) )
          {
            // all gauges are ready to be filled... let's goooo
            $(form).clone(true).removeAttr('onsubmit').appendTo('body').submit().remove();
            setTimeout(function(){ li.initContent(); }, 1000);
          }
        }
      });
    }
  });
  
  return false;
}

// display a flash for a limited time
li.alert = function(msg, type = 'notice', time = 4000)
{
  var icons = {
    success: 'ui-icon-circle-check',
    notice:  'ui-icon-info',
    error:   'ui-icon-alert',
  }
  var flash = '<div class="%%type%% ui-state-highlight ui-corner-all"><span class="ui-icon %%icon%% floatleft"></span>&nbsp;%%msg%%</div>';
  
  $('.sf_admin_flashes').append($(flash.replace('%%msg%%',msg).replace('%%type%%',type).replace('%%icon%%', icons[type])).hide().fadeIn('slow'));
  setTimeout(function(){
    $('.sf_admin_flashes > *').fadeOut(function(){ $(this).remove(); })
  },time);
}

li.renderGauge = function(item, only_inline_gauge)
{
  if ( only_inline_gauge == undefined )
    only_inline_gauge = false;
  
  // the small gauge
  if ( $(item).find('.data .gauge.raw').length > 0 )
  {
    data = JSON.parse($(item).find('.data .gauge.raw').html());
    var total = data.total > data.booked.printed + data.booked.ordered + data.booked.asked
      ? data.total
      : data.booked.printed + data.booked.ordered + data.booked.asked;
    $('<div></div>').addClass('gauge').addClass('raw')
      .appendTo($('#li_transaction_field_product_infos'))
      .append($('<span></span>').addClass('printed').css('width', (data.booked.printed/total*100)+'%').html(data.booked.printed).prop('title',data.booked.printed))
      .append($('<span></span>').addClass('ordered').css('width', (data.booked.ordered/total*100)+'%').html(data.booked.ordered).prop('title',data.booked.ordered))
      .append($('<span></span>').addClass('asked')  .css('width', (data.booked.asked  /total*100)+'%').html(data.booked.asked).prop('title', data.booked.asked))
      .append($('<span></span>').addClass('free')   .css('width', ((data.free < 0 ? 0 : data.free)/total*100)+'%').html(data.free).prop('title',data.free))
      .prepend($('<span></span>').addClass('text').html('<span class="total">'+data.txt+'</span> <span class="details">'+data.booked_txt+'</span>'));
    ;
  }
  
  // gauge for seated plan
  if ( !only_inline_gauge && $(item).find('.data .gauge.seated').length > 0 )
  {
    if ( $(item).find('.data .gauge.seated.picture').length > 0 )
    {
      // cache
      $(item).find('.data .gauge.seated.picture').clone(true)
        .appendTo($('#li_transaction_field_product_infos'));
    }
    else
    {
      // remote loading
      $(item).find('.data .gauge.seated').clone(true)
        .addClass('picture').addClass('seated-plan')
        .appendTo($('#li_transaction_field_product_infos'));
      
      li.seatedPlanInitializationFunctions.push(function(){
        var elt = $('#li_transaction_field_product_infos .gauge.seated.picture');
        var scale = ($('#li_transaction_field_product_infos').width()-15)/$(elt).width();
        
        $(elt)
          .css('transform', 'scale('+scale+')') // the scale
          .css('margin-bottom', -$('#li_transaction_field_product_infos .gauge.seated').height()*(1-scale)+10) // the margin bottom
          .clone(true)
          .appendTo($(item).find('.data'));
      });
      li.seatedPlanInitialization($('#li_transaction_field_product_infos'));
    }
    
    setTimeout(function(){
    },700); // arbitrary...
  }
}

li.responsiveDesign = function(){
  $(window).resize(function(){
    var margin;
    $('#sf_admin_content').css('transform', 'scale(1)');
    
    var scale = {
      x: $('#sf_admin_container').width()/$('#sf_admin_content').width(),
      y: ( $(window).height()
          - $('#sf_admin_content').position().top
         )/$('#sf_admin_content').height()
    };
    
    // if gap between the two scales is too important, choose the smallest
    if ( scale.x / scale.y > 1.3 )
      scale.x = scale.y * 1.3;
    if ( scale.y / scale.x > 1.3 )
      scale.y = scale.x * 1.3;
    
    $('#sf_admin_content').css('transform', 'scale('+scale.x+','+scale.y+')');
    
    $('#sf_admin_container').height(
        $('#sf_admin_content').height()*scale.y + 20
      + $('.ui-widget-header').height()
      + $('#sf_admin_header').height()
    );
  }).resize();
}

li.initContent = function(){
  $.each(li.urls, function(id, url){
    $.get(url,function(data){
      if ( data.error[0] )
      {
        li.alert(data.error[1],'error');
        return;
      }
      if (!( data.success.error_fields !== undefined && data.success.error_fields[id] === undefined ))
      {
        li.alert(data.success.error_fields[id],'error');
        return;
      }
      
      if ( data.success.success_fields[id] !== undefined && data.success.success_fields[id].data !== undefined )
      {
        li.completeContent(data.success.success_fields[id].data.content, id);
      }
    });
  });
}

// GENERIC FORMS INITIALIZATION
li.initTouchscreen = function(elt)
{
  switch ( elt ) {
  case '#li_transaction_field_contact_id':
    if ( $(elt+' [name="transaction[contact_id]"]').val() == '' )
      $(elt+' .data a').remove();
    else
      $(elt+' .data a').prepend('<span class="ui-icon ui-icon-person"></span>');
    $(elt+' .li_touchscreen_new').toggle($(elt+' .data a').length == 0);
    $('#li_transaction_field_informations .vcard').remove();
    $(elt).click();
    break;
  
  case '#li_transaction_field_professional_id':
    if ( $(elt+' select option').length == 0 || $(elt+' select option').length == 1 && !$(elt+' select option:first').val() )
      $(elt+' select').fadeOut('fast');
    else
      $(elt+' select').fadeIn('medium');
    break;
  }
}

// THE CURRENCY
li.format_currency = function(value, nbsp, nodot)
{
  if ( nbsp  == undefined ) nbsp  = true;
  if ( nodot == undefined ) nodot = true;
  if ( !value ) value = 0;
  
  var r = $('.currency:first').length > 0
    ? $('.currency:first').html()
    : '%d €';
  value = r.replace('%d',value.toFixed(2));
  
  if ( nbsp  ) value = value.replace(' ','&nbsp;');
  if ( nodot ) value = value.replace('.',',');
  
  return value;
}

// THE TOTALS
li.calculateTotals = function()
{
  if ( $(this).closest('.families.sample').length > 0 )
   return;
    
  // remove totals if there is only one line
  if ( !$(this).closest('.family').is('.total')
    && $(this).closest('.declinations').length > 0
    && $(this).closest('.declinations').find('.declination').length <= 1 )
  {
    if ( $(this).closest('.declinations').is('.total') )
    {
      if ( $(this).closest('.items').find('.item:not(.total) .declinations').length <= 1 )
        $(this).closest('.item').hide();
    }
    else
      $(this).hide();
  }
    
  var elt = this;
  var totals = new Object;
  $(this).closest($(this).closest('.declinations.total').length > 0 ? '.items' : '.declinations').find('.declination .nb').each(function(){
    var val = $(this).is('.qty') ? $(this).find('input').val() : $(this).html();
    if ( !val ) val = '';
    
    if ( totals[$(this).attr('class')] == undefined )
      totals[$(this).attr('class')] = 0;
    
    i = parseFloat(val.replace(',','.'));
    if ( !isNaN(i) )
      totals[$(this).attr('class')] += i;
  });
  
  $.each(totals, function(index, value){
    var total = $(elt).find('.'+index.replace(/\s+/g,'.'));
    if ( $(total).hasClass('monney') )
      value = li.format_currency(value);
    if ( total.is('.qty') )
      total.find('.qty').html(value);
    else
      total.html(value);
  });
    
  // megatotal
  var megaelt = $(this).closest('.families').find('.family.total');
  totals = new Object;
  $(this).closest('.families').find('.family:not(.total) .item.total .nb').each(function(){
    var val = $(this).is('.qty') ? $(this).find('.qty').html() : $(this).html();
    if ( totals[$(this).attr('class')] == undefined )
      totals[$(this).attr('class')] = 0;
    i = parseFloat(val.replace(',','.'));
    if ( !isNaN(i) )
      totals[$(this).attr('class')] += i;
  });
  $.each(totals, function(index, value){
    var total = $(megaelt).find('.'+index.replace(/\s+/g,'.'));
    if ( $(total).hasClass('monney') )
      value = li.format_currency(value);
    if ( total.is('.qty') )
      total.find('.qty').html(value);
    else
      total.html(value);
  });
  
  // total of totals
  var total = { pit: 0, vat: 0, tep: 0 };
  $('.family.total .item.total tr.total').each(function(){
    var family = this;
    $.each(total, function(index, value){
      var tmp = parseFloat($(family).find('.'+index).html().replace(',','.'));
      if ( !isNaN(tmp) )
        total[index] += tmp;
    });
  });
  $.each(total, function(index, value){
    $('#li_transaction_field_payments_list .topay .'+index).html(li.format_currency(value));
    
    var tmp = parseFloat($('#li_transaction_field_payments_list tfoot .total .sf_admin_list_td_list_value').html());
    tmp = isNaN(tmp) ? 0 : tmp;
    tmp = total[index] - tmp * total[index]/total.pit;
    tmp = isNaN(tmp) ? 0 : tmp;
    $('#li_transaction_field_payments_list .change .'+index)
      .html(li.format_currency(tmp));
  });
}

// function to go back to the ticketting transaction from the contact window
li.goBackToTransaction = function(){
  setTimeout(function(){
    $(w.document).ready(function(){
    if ( contact_id = $(w.document).find('[name="contact[id]"]').val() )
    {
      $('#li_transaction_field_contact_id [name="transaction[contact_id]"]').val(contact_id);
      $('#contact form').submit();
      w.close();
    }
    else
    {
      // one level deeper through the dream layers
      $(w.document).ready(function(){
        $(w.document).find('.sf_admin_actions_form .sf_admin_action_list, .sf_admin_actions_form .sf_admin_action_save_and_add')
          .remove();
      });
      w.onunload = li.goBackToTransaction;
    }
    });
  },2500);
}

// click on virtual keyboard
li.clickBoard = function(){
  var elt = $('.li_fieldset .ui-state-highlight').find('textarea, input:not([type=hidden])');
  if ( $('.li_fieldset .ui-state-highlight').closest('#li_transaction_field_content').length == 1 )
    elt = $('#li_transaction_field_price_new').find('input[type=text]'); // case of qty of "products"
  
  if ( $(this).val().substring(0,1) != '_' )
  {
    if ( !$(this).closest('#li_transaction_field_board').hasClass('alpha') )
      elt.val(elt.val()+parseInt($(this).find('.num').html(),10)); // num
    else // alpha
    {
      var button = this; // init
      
      if ( $(button).hasClass('selected') )
      {
        // same button
        var index = $(button).val().indexOf($(button).prop('title'))+1;
        var letter = $(button).val().substring(index, index+1);
        if ( !letter )
          letter = $(button).val().substring(0,1);
        
        $(button).prop('title', letter);
      }
      else
      {
        // changing button
        if ( $('#li_transaction_field_board .selected').length > 0 )
          elt.val(elt.val()+$('#li_transaction_field_board .selected').prop('title'));
        $('#li_transaction_field_board .selected').removeClass('selected').prop('title',false);
        
        // recording the current one...
        $(button).addClass('selected').prop('title',$(button).val().substring(0,1));
      }
      
      // completion
      setTimeout(function(){
        if ( $(button).is('.selected') )
        {
          elt.val(elt.val()+$(button).prop('title'));
          $('#li_transaction_field_board .selected').removeClass('selected').prop('title',false);
        }
      },500);
    }
  }
  else
  {
    switch ( $(this).val() ) {
    case '_ACTION_':
      if ( elt.is('textarea') )
        elt.val(elt.val()+"\n");
      else
        elt.keydown();
        elt.closest('form').submit();
      break;
    case '_BACKSPACE_':
      elt.val(elt.val().substring(0,elt.val().length-1));
      break;
    }
  }
  elt.focus();
}

// DISPLAYS A WARNING MESSAGE WHEN THE WINDOW ATTEMPS TO BE CLOSED
li.closeTransaction = function(event){
  $('#transition').show();
  var go = { ok: true, text: '' };
  
  $.ajax({
    url: $('#li_transaction_field_close form').prop('action'),
    type: $('#li_transaction_field_close form').prop('method'),
    data: $('#li_transaction_field_close form').serialize(),
    async: false,
    success: function(data){
      if ( data.error[0] )
      {
        go.ok   = error[0];
        go.text = error[1];
        return;
      }
      var buf = [];
      
      // success
      if ( data.success.success_fields.close !== undefined )
      {
        go.ok   = true;
        $.each(data.success.error_fields.close.data, function(index, text){
          buf.push(text);
        });
        go.text = buf.join("\n");
        return;
      }
      
      // error
      if ( data.success.error_fields.close !== undefined )
      {
        go.ok   = false;
        $.each(data.success.error_fields.close.data, function(index, text){
          buf.push(text);
        });
        buf.push('');
        buf.push($('#li_transaction_field_close .confirmation').text());
        go.text = buf.join("\n");
        return;
      }
    }
  });
  
  // the GUI behaviour
  if ( !go.ok && !confirm(go.text) )
  {
    setTimeout(function(){ window.stop(); }, 1);
    $('#transition .close').click();
  }
}

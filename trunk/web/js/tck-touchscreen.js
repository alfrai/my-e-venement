$(document).ready(function(){
  // AUTO VALIDATIONS
  $('[name="transaction[contact_id]"]'+
  ', [name="transaction[professional_id]"]'+
  ', [name="transaction[description]"]')
    .change(function(){ $(this).closest('form').submit(); });
  
  // ALL FORMS VALIDATION
  $('#sf_admin_content form').submit(function(){
    var form = this;
    $.ajax({
      url: $(this).prop('action'),
      data: $(this).serialize(),
      type: $(this).prop('method'),
      success: function(data){
        // main error
        if ( data.error[0] )
        {
          alert(data.error[1]);
          return;
        }
        
        // detailed errors
        var msg = '';
        $.each(data.success.error_fields, function( index, value ){
          msg += index+': '+value+"\n";
        });
        if ( msg ) alert(msg);
        
        // successes
        $.each(data.success.success_fields, function(index, value){
          var elt = '#li_'+data.base_model+'_field_'+index;
          var content = $(elt).find('.data').length > 0 && value.content != undefined;
          
          $(elt).find('.data').remove();
          $(elt).append('<div class="data"></div>');
          
          // if link
          if ( content && value.content.url != undefined && value.content.text != undefined )
          {
            $('<a></a>').prop('href', value.content.url).prop('target', '_blank')
              .html(value.content.text)
              .appendTo($(elt).find('.data'));
          }
          
          // any data to play with
          if ( value.data && value.data.type )
          switch ( value.data.type ) {
          case 'gauge_price':
            if ( !value.data.reset )
              return;
            
            elt = $('#li_transaction_gauge_'+value.data.gauge_id+' .declination'+(value.data.printed ? '.printed' : ':not(.printed)')+'[data-price-id='+value.data.price_id+']');
            if ( value.data.qty > 0 )
            {
              elt.find('.qty input').val(value.data.qty).select();
              elt.closest('.item').find('.total').select();
            }
            else
              elt.remove();
            
            break;
          }
          
          // any select's options to add
          if ( value.content && value.content.load )
          switch ( value.content.load.type ) {
          case 'gauge_price':
            $.ajax({
              url: value.content.load.url,
              complete: function(data){ form.pending = undefined; },
              success: function(data){ liCompleteContent(data, 'manifestations', false); }
            });
            break;
          case 'options':
            var select = value.content.load.target ? $(value.content.load.target) : $(form).find('select:first');
            
            if ( value.content.load.reset ) // reset
              select.find('option:not(:first-child)').remove();
            
            if ( value.content.load.data ) // complete
            $.each(value.content.load.data, function(index, value){
              $('<option />').val(index).html(value)
                .appendTo(select);
            });
            
            // default val
            if ( value.content.load.default )
              select.val(value.content.load.default);
            
            // init an other widget
            var sel = value.content.load.target.replace(/^(.*)\s.*$/, '$1');
            if ( sel != elt ) touchscreen_init(sel);
            
            break;
          }
          
          touchscreen_init(elt);
        });
      }
    });
    
    // debug purposes
    if ( location.hash === '#debug' )
      return true;
    
    return false;
  });
  
  // GENERIC FORMS INITIALIZATION
  function touchscreen_init(elt)
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
  
  // changing quantities
  $('#li_transaction_field_content .qty a').click(function(){ var input = $(this).closest('.qty').find('input'); input.val(parseInt(input.val(),10)+($(this).is(':first-child') ? -1 : 1)).change(); });
  $('#li_transaction_field_content .qty input').focusout(function(){ return false; }).select(function(){
    $(this).prop('defaultValue',$(this).val()); // quite useless... but whatever
  }).change(function(){
    $(this).closest('.highlight').focusin();
    if ( isNaN(parseInt($(this).val(),10)) )
      $(this).val($(this).prop('defaultValue'));
    
    if ( $(this).prop('defaultValue') !== $(this).val() )
    {
      var form = $('#li_transaction_field_price_new form');
      var orig = form.find('[name="transaction[price_new][qty]"]').val();
      
      // set values & subit
      form.find('[name="transaction[price_new][qty]"]').val($(this).val() - $(this).prop('defaultValue'));
      form.find('[name="transaction[price_new][price_id]"]').val($(this).closest('.declination').attr('data-price-id'));
      form.find('[name="transaction[price_new][gauge_id]"]').val($(this).closest('.item').attr('data-gauge-id'));
      form.submit();
        
      // reinit
      form.find('[name="transaction[price_new][qty]"]').val(orig);
    }
  });
  
  // total calculation
  $('#li_transaction_field_content .item .total').select(function(){
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
      if ( totals[$(this).attr('class')] == undefined )
       totals[$(this).attr('class')] = 0;
      
      i = parseFloat(val.replace(',','.'));
      if ( !isNaN(i) )
        totals[$(this).attr('class')] += i;
    });
    // total of subtotals
    var currency = $(elt).closest('.item').find('.currency').html();
    $.each(totals, function(index, value){
      var total = $(elt).find('.'+index.replace(/\s+/g,'.'));
      if ( $(total).hasClass('monney') )
        value = value.toFixed(2)+' '+currency;
      if ( total.is('.qty') )
        total.find('.qty').html(value);
      else
        total.html(value);
    });
    
    // megatotal
    var megaelt = $('#li_transaction_field_content .families:not(.sample) .family.total');
    totals = new Object;
    $('#li_transaction_field_content .families:not(.sample) .family:not(.total) .item.total .nb').each(function(){
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
        value = value.toFixed(2)+' '+currency;
      if ( total.is('.qty') )
        total.find('.qty').html(value);
      else
        total.html(value);
    });
  }).select();
  
  // CONTACT CHANGE & INIT
  $.each([
    '#li_transaction_field_contact_id',
    '#li_transaction_field_professional_id'
  ], function(index, value){ touchscreen_init(value); });
  
  // CONTACT CREATION
  $('#li_transaction_field_contact_id .create-contact').click(function(){
    var w = window.open($(this).prop('href')+'&name='+$('#li_transaction_field_contact_id [name="autocomplete_transaction[contact_id]"]').val(),'new_contact');

    // function to go back to the ticketting transaction from the contact window
    function ticket_go_back_to_transaction(){
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
            w.onunload = ticket_go_back_to_transaction;
          }
        });
      },2500);
    };
    
    w.onload = function(){
      setTimeout(function(){
        $(w.document).ready(function(){
          $(w.document).find('.sf_admin_actions_form .sf_admin_action_list, .sf_admin_actions_form .sf_admin_action_save_and_add')
            .remove();
        });
        w.onunload = ticket_go_back_to_transaction;
      },2500);
    };
    
    return false;
  });
  
  // CLICK WIDGETS
  $('#sf_admin_content .highlight').focusout(function(){
    $(this).removeClass('ui-state-highlight');
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
  $('#li_transaction_field_board button').click(function(){
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
  });
  
  // make the flashes to disapear
  setTimeout(function(){ $('.sf_admin_flashes > *').fadeOut('slow',function(){ $(this).remove(); }); }, 3500);
  
  // RESPONSIVE DESIGN
  $(window).resize(function(){
    var margin;
    var scale = $(window).width()/1050;
    $('#sf_admin_content').css('transform', 'scale(1)');
    var dimensions = [$('#sf_admin_content').width(), $('#sf_admin_content').height()];
    
    $('#sf_admin_content')
      .css('transform', 'scale('+scale+')')
      .css('margin-left', (dimensions[0]*(scale-1)/2)+'px')
      .css('margin-top', (margin = dimensions[1]*(scale-1)/2)+'px')
    ;
    
    $('#sf_admin_container').height(
        $('#sf_admin_content').height()*scale + Math.abs(margin/2) + 20
      + $('.ui-widget-header').height()
      + $('.sf_admin_flashes').height()
      + $('#sf_admin_header').height()
    );
  }).resize();
});

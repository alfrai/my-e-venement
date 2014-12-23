  $(document).ready(function(){
    var form = $('#li_transaction_field_price_new form.prices');
    $('#li_transaction_field_price_new').click(function(){
      $(this).find('input[type=text]').focus();
    });
    
    // tickets with an "origin"
    $('#li_transaction_field_more .origin input').change(function(){
      if ( parseInt($(this).val(),10)+'' === $(this).val() )
        $('#li_transaction_field_price_new input[name="transaction[price_new][origin]"]').val($(this).val());
    });
    
    // dealing w/ the GUI for cancellations
    $('#li_transaction_field_price_new .cancel').click(function(){
      var url = $(this).prop('href');
      $(this).prop('href', form.prop('action'));
      form.prop('action', url);
      $('#li_transaction_field_price_new').toggleClass('cancelling').find('form.prices').toggleClass('noajax');
      $('#li_transaction_field_price_new').find('a, input, button').unbind('focusout').focusout(function(){ return false; });
      $('#li_transaction_field_price_new [name="transaction[price_new][qty]"]').focus();
      return false;
    });
    $('.highlight').focusin(function(){
      if ( $('#li_transaction_field_price_new').hasClass('cancelling') )
        $('#li_transaction_field_price_new .cancel').click();
    });
    
    // dealing w/ the "seats-first" feature
    $('#li_transaction_field_price_new .seats-first').unbind();
    
    // dealing w/ the "dispatching" feature
    $('#li_transaction_field_price_new .dispatch').unbind();
    $('#li_transaction_field_price_new .dispatch [name=prepare]').click(function(){
      $(this).closest('form').find('input').toggle();
      $('#li_transaction_field_content .item.highlight .ids span').each(function(){
        $('<input type="checkbox" />').prop('name','dispatch[]').val($(this).attr('data-id'))
          .click(function(event){ event.stopImmediatePropagation(); })
          .prependTo($(this));
      });
      return false;
    });
    $('#li_transaction_field_price_new .dispatch [name=dispatch]').click(function(){
      var form = $(this).closest('form');
      if ( $('#li_transaction_field_content .item .ids input:checked').length == 0 )
        return false;
      $('#li_transaction_field_content .item .ids input:checked').each(function(){
        form.append($(this).clone());
      });
    });
    
    $('#li_transaction_field_content .highlight:not(.new-family)').focusin(function(){
      form.find('button').remove();
      var item = this;
      var available_prices = JSON.parse($.trim($(this).find('.data .available_prices').text()));
      $.each(available_prices, function(i, price){
        var price = price;
        $('<button name="price_new[id]"></button>')
          .val(price.id)
          .html(price.name)
          .prop('title', (price.value !== null ? price.value : $('#li_transaction_field_close .prices .free-price').text())+' - '+price.description)
          .attr('data-'+$(item).attr('data-type')+'-id', $(item).attr('data-'+$(item).attr('data-type')+'-id'))
          .attr('data-type', $(item).attr('data-type'))
          .appendTo(form.find('p'))
          .click(function(){
            var qty = $(this).closest('form').find('[name="transaction[price_new][qty]"]').val();
            if ( price.value === null && (parseInt(qty,10) > 0 || qty === '') )
            {
              var amount = prompt($('#li_transaction_field_close .prices .free-price').text(), parseFloat($('#li_transaction_field_close .prices .free-price-default').text()))
              if ( isNaN(parseFloat(amount)) )
                return false;
              $(this).closest('form').find('[name="transaction[price_new][free-price]"]').val(parseFloat(amount));
            }
            $(this).closest('form').find('[name="transaction[price_new][price_id]"]')
              .val($(this).val());
            $(this).closest('form').find('[name="transaction[price_new][declination_id]"]')
              .val($(this).attr('data-'+$(this).attr('data-type')+'-id'));
            $(this).closest('form').find('[name="transaction[price_new][type]"]')
              .val($(this).attr('data-type'));
          })
        ;
      });
      
      // direct seating
      if ( $(this).is('[data-gauge-id]') )
      {
        if ( $(this).find('.data .gauge.seated').length > 0 )
          $('#li_transaction_field_price_new .seats-first').addClass('usefull');
        else
          $('#li_transaction_field_price_new .seats-first').removeClass('usefull');
        $('#li_transaction_field_price_new .seats-first [name=gauge_id]').val($(this).attr('data-gauge-id'));
      }
      
      $('#li_transaction_field_price_new').fadeIn();
    }).focusout(function(){
      var elt = this;
      setTimeout(function(){
        if ( $('#li_transaction_field_content .ui-state-highlight').length == 0 )
        {
          $('#li_transaction_field_price_new').fadeOut();
          $('#li_transaction_field_price_new .dispatch input').toggle();
          $('#li_transaction_field_content .item.highlight .ids input').remove();
        }
        if ( !$('#li_transaction_field_content .ui-state-highlight').is(elt) )
          $('#li_transaction_field_content .item.highlight .ids').removeClass('show');
      },100);
    });
  });

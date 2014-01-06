  $(document).ready(function(){
    var form = $('#li_transaction_field_price_new form');
    $('#li_transaction_field_price_new').click(function(){
      $(this).find('input[type=text]').focus();
    });
    
    $('#li_transaction_field_content .highlight:not(.new-family)').focusin(function(){
      form.find('button').remove();
      var item = this;
      var available_prices = JSON.parse($.trim($(this).find('.data .available_prices').text()));
      $.each(available_prices, function(i, price){
        $('<button name="price_new[id]"></button>')
          .val(price.id)
          .html(price.name)
          .prop('title', price.value+' - '+price.description)
          .attr('data-gauge-id', $(item).attr('data-gauge-id'))
          .appendTo(form.find('p'))
          .click(function(){
            $(this).closest('form').find('[name="transaction[price_new][price_id]"]')
              .val($(this).val());
            $(this).closest('form').find('[name="transaction[price_new][gauge_id]"]')
              .val($(this).attr('data-gauge-id'));
          })
        ;
      });
      
      $('#li_transaction_field_price_new').fadeIn();
    }).focusout(function(){
      setTimeout(function(){
        if ( $('#li_transaction_field_content .ui-state-highlight').length == 0 )
          $('#li_transaction_field_price_new').fadeOut();
      },100);
    });
  });

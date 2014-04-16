  $(document).ready(function(){
    var families = $('#li_transaction_field_content .new-family select');
    
    $('#li_transaction_field_content .new-family select').focusout(function(){
      if ( $(this).val() )
      {
        $(this).closest('form').submit();
        if ( $('#li_transaction_field_new_transaction a').hasClass('persistant') )
          $('#li_transaction_field_new_transaction a').prop('href', $('#li_transaction_field_new_transaction a').prop('href')+'#'+$(this).closest('.bunch').prop('id').replace('li_transaction_','')+'-'+$(this).val()); // keep the same manifestations for the next transaction
        $(this).find('option:selected').remove();
      }
    });
    
    li.autoAddFamilies();
    
    // the autocompleter & the manifestation's selector
    $('#li_transaction_field_content .new-family [name=autocompleter]').keyup(function(e){
      var val = $(this).val();
      var elt = this;
      setTimeout(function(){
        if ( val == $(elt).val() ) // then launch the request
        {
          // emptying the previous select's content
          families.html('');
          
          // disabling the selection of any manif that is already selected (including those w/o any ticket yet) 
          var except = [];
          $(elt).closest('.bunch').find('.family:not(.total)').each(function(){
            if ( $(this).attr('data-family-id') )
              except.push($(this).attr('data-family-id'));
          });
        
          $.ajax({
            url: families.attr('data-content-url'),
            data: { with_colors: true, q: $(elt).val(), except: except, max: families.attr('data-content-qty'), 'keep-order': true },
            method: 'get',
            success: function(data){
              families.html('');
              $.each(data, function(id, manif){
                $('<option></option>').css('background-color', manif.color).val(manif.id)
                  .html(manif.name).prop('title', manif.name)
                  .appendTo(families);
              });
            }
          });
        }
      },330);
      
      return false;
    }).keyup();
    
    // REMOVE A FAMILY FROM THE LIST IF THERE IS NO ITEM INSIDE
    $('#li_transaction_field_content .family h3 .fg-button').click(function(){
      var can_be_deleted = true;
      $(this).closest('.family').find('.qty input').each(function(){
        if ( $(this).val() && !isNaN(parseInt($(this).val())) && parseInt($(this).val()) > 0 )
        {
          $(this).addClass('blink');
          can_be_deleted = false;
        }
      });
      
      if ( can_be_deleted )
      {
        $('<option></option>')
          .val($(this).closest('.family').attr('data-family-id'))
          .html($(this).closest('.family').find('h3').text())
          //.prop('title',$(this).closest('.family').find('h3').text().replace("\n",''))
          .css('background-color', $(this).closest('.family').find('h3').css('background-color'))
          .appendTo($(this).closest('.bunch').find('.new-family select'));
        console.log('added');
        $('#li_transaction_field_new_transaction a').prop('href', $('#li_transaction_field_new_transaction a').prop('href').replace(
          '#'+$(this).closest('.bunch').prop('id').replace('li_transaction_','')+'-'+$(this).closest('.family').attr('data-family-id'),
          ''
        ));
        $(this).closest('.family').remove();
      }
      else
      {
        var elts = $(this).closest('.family').find('.qty.blink');
        li.blinkQuantities(elts);
      }
      
      return false;
    });
  });

li.blinkQuantities = function(elts, full = false){
  elts.blink = 0;
  
  var blink = function(){
    if ( elts.blink % 2 == 0 )
      elts.css('border-color', 'red').css('color', full ? 'red' : '');
    else
      elts.css('border-color', '').css('color', '');
    
    if ( elts.blink < 7 )
      setTimeout(blink,500);
    else
      elts.removeClass('blink');
    elts.blink++;
  }
  blink();
}

// add automatically manifestations to the current transaction
li.autoAddFamilies = function(form){
  $(location.hash.split('#')).each(function(key, value){
    if ( !value )
      return;
    type = value.replace(/-\d+$/,'');
    id = value.replace(/^\w+-/,'');

    switch ( type ) {
    case 'manifestations':
      $('#li_transaction_manifestations .new-family [name="manifestation_id[]"] *').remove();
      $('#li_transaction_manifestations .new-family [name="manifestation_id[]"]')
        .append($('<option>'+id+'</option>').val(id).prop('selected',true))
        .focusout();
      break;
    }
  });
}


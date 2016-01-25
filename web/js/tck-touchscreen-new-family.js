  $(document).ready(function(){
    $('#li_transaction_field_content .new-family select').focusout(function(){
      LI.addFamilies(this);
    });
    LI.autoAddFamilies();
    
    // the autocompleter & the manifestation's selector
    $('#li_transaction_field_content .new-family [name=autocompleter]').keyup(function(e){
      var val = $(this).val();
      var elt = this;
      setTimeout(function(){ // this timeout is to execute a HTTP request for a bulk for letters instead of for every single keyup()
        if ( val == $(elt).val() ) // then launch the request
        {
          var select = $(elt).closest('.new-family').find('select');
          
          // emptying the previous select's content
          select.html('');
          
          // disabling the selection of any manif that is already selected (including those w/o any ticket yet) 
          var except = [];
          $(elt).closest('.bunch').find('.family:not(.total)').each(function(){
            if ( $(this).attr('data-family-id') )
              except.push($(this).attr('data-family-id'));
          });
          
          $.ajax({
            url: select.attr('data-content-url'),
            data: { with_colors: true, q: $(elt).val(), except: except, max: select.attr('data-content-qty'), 'keep-order': true },
            method: 'get',
            success: function(data){
              select.html('');
              $.each(data, function(id, manif){
                $('<option></option>').val(manif.id)
                  .css('background-color', manif.color)
                  .text(manif.name).prop('title', manif.name)
                  .attr('data-gauge-url', manif.gauge_url)
                  .appendTo(select);
              });
              
              // if only one option is available w/o looking for something special, select this only option
              if ( $(elt).val() == '' && select.find('option').length == 1
                && location.hash != '#debug'
                && $(elt).closest('.bunch').find('.family:not(.total)').length == 0
              )
              {
                select.find('option').prop('selected', true);
                select.focusout();
              }
              
              // show mini-gauge w/o selecting a new family
              $('#li_transaction_manifestations .new-family select option').unbind('click').click(function(){
                if ( !$(this).attr('data-gauge-url') )
                  return;
                $.get($(this).attr('data-gauge-url'), function(data){
                  LI.renderGauge(JSON.stringify(data), true);
                });
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
        $('#li_transaction_field_new_transaction a.persistant').prop('href', $('#li_transaction_field_new_transaction a.persistant').prop('href').replace(
          '#'+$(this).closest('.bunch').prop('id').replace('li_transaction_','')+'-'+$(this).closest('.family').attr('data-family-id'),
          ''
        ));
        $(this).closest('.family').remove();
      }
      else
      {
        var elts = $(this).closest('.family').find('.qty.blink');
        LI.blinkQuantities(elts);
      }
      
      return false;
    });
  });

LI.blinkQuantities = function(elts, full = false){
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
LI.autoAddFamilies = function(form){
  $(location.hash.split('#')).each(function(key, value){
    if ( !value )
      return;
    var type = value.replace(/-\d+(,\d+)*$/,'');
    var id = value.replace(/^\w+-/,'');

    switch ( type ) {
    case 'manifestations':
      $('#li_transaction_manifestations .new-family [name="manifestation_id[]"] *').remove();
      $(id.split(',')).each(function(i, v){
        $('#li_transaction_manifestations .new-family [name="manifestation_id[]"]')
          .append($('<option>'+v+'</option>').val(v).prop('selected',true));
      });
      LI.addFamilies($('#li_transaction_manifestations .new-family [name="manifestation_id[]"]'));
      break;
    }
  });
}

LI.addFamilies = function(elt){
  elt == undefined
    ? $('#li_transaction_field_content .new-family select')
    : $(elt).closest('.new-family').find('select')
  ;
  
  if ( $(elt).val() )
  {
    var nf = $(elt).closest('.new-family');
    var bunch = nf.closest('.bunch');
    
    nf.submit();
    nf.find('select option:selected').remove();
    
    setTimeout(function(){
      bunch.find('.families:not(.sample) .family:not(.total):last .item:first').click();
    }, 1000);
  }
}

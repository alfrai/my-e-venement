  $(document).ready(function(){
    var families = $('#li_transaction_field_content .new-family select');
    
    $('#li_transaction_field_content .new-family select').focusout(function(){
      if ( $(this).val() )
      {
        $(this).closest('form').submit();
        $(this).find('option:selected').remove();
      }
    });
    
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
            if ( $(this).attr('data-manifestation-id') )
              except.push($(this).attr('data-manifestation-id'));
          });
        
          $.ajax({
            url: families.attr('data-content-url'),
            data: { with_colors: true, q: $(elt).val(), except: except },
            method: 'get',
            success: function(data){
              families.html('');
              $.each(data, function(id, manif){
                $('<option></option>').css('background-color', manif.color).val(id).html(manif.name)
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
        $(this).closest('.family').remove();
      else
      {
        var elts = $(this).closest('.family').find('.qty.blink');
        elts.blink = 0;
        
        var blink = function(){
          if ( elts.blink % 2 == 0 )
            elts.css('border-color', 'red');
          else
            elts.css('border-color', '');
          
          if ( elts.blink < 3 )
            setTimeout(blink,500);
          else
            elts.removeClass('blink');
          elts.blink++;
        }
        blink();
      }
      
      return false;
    });
  });

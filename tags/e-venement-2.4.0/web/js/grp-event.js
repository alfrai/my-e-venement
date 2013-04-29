$(document).ready(function(){
  
  $(document).mouseup(function(){
    $('.grp-entry tbody .copy').removeAttr('title');
    
    $('.grp-entry tbody .copy').removeClass('copy');
    $('.grp-entry tbody').removeClass('move');
    
    $('.grp-entry tbody').unbind('mousemove');
  });
  
  $('.grp-entry tbody td:not(.contact):not(.ticketting)').unbind().mouseup(function(){
    // preventing gauges' multi-calculation
    $('body').append('<div id="no-calculate-gauge"></div>');
    
    src = $('.grp-entry .copy');
    if ( !$(this).hasClass('copy')
      && src.length == 1 )
    {
      target = $(this);
      
      // deleting everything
      target.find('a.delete').click(); 
      target.find('form:last input[type=checkbox]:checked').click();
          
      // adding tickets
      src.find('form').each(function() {
        if ( $(this).find('input[type=text]').length > 0 && $(this).find('input[type=text]').val() != '' )
        {
          target.find('form:first input[type=text]').val($(this).find('input[type=text]').val());
          target.find('form:first select').val($(this).find('select').val());
          target.find('form:first').submit();
        }
      });
      
      // global statute
      if ( src.find('form:last input[type=checkbox]:first').prop('checked') )
        target.find('form:last input[type=checkbox]:first').click();
      if ( src.find('form:last input[type=checkbox]:last').prop('checked') )
        target.find('form:last input[type=checkbox]:last').click();
      target.find('form:last').submit();
      
      // deleting source
      src.find('a.delete').click();
      src.find('form:last input[type=checkbox]:checked').click();
      src.find('form').submit();
    }
    
    $('.grp-entry tbody .copy').removeAttr('title');
    $('.grp-entry tbody .copy').removeClass('copy');
    $('.grp-entry tbody').removeClass('move');
    $('.grp-entry tbody').unbind('mousemove');
    
    // recalculate gauges
    setTimeout(function(){
      $('#no-calculate-gauge').remove();
      calculate_gauges();
    },2000);
  });
  
  $('.grp-entry tbody td:not(.ticketting):not(.contact)').mousedown(function(){
    $(this).addClass('copy');
    $('.grp-entry tbody').addClass('move');
    
    $(this).prop('title',$('#copy-paste').html());
    $('.grp-entry tbody').mousemove(grp_mouse_move);
  });
  
  // the titles for manifestations' actions
  $('.manifestation .fg-button-mini').each(function(){
    $(this).prop('title',$.trim($(this).html()));
  });
                  
});

function grp_mouse_move(event)
{
}

$(document).ready(function(){
  
  $(document).mouseup(function(){
    $('.grp-entry tbody .copy').removeAttr('title');
    
    $('.grp-entry tbody .copy').removeClass('copy');
    $('.grp-entry tbody').removeClass('move');
    
    $('.grp-entry tbody').unbind('mousemove');
  });
  
  $('.grp-entry tbody td:not(.contact):not(.ticketting)').unbind().mouseup(function(){
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
      if ( src.find('form:last input[type=checkbox]:first').attr('checked') )
        target.find('form:last input[type=checkbox]:first').click();
      if ( src.find('form:last input[type=checkbox]:last').attr('checked') )
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
  });
  
  $('.grp-entry tbody td:not(.ticketting):not(.contact)').mousedown(function(){
    $(this).addClass('copy');
    $('.grp-entry tbody').addClass('move');
    
    $(this).attr('title',$('#copy-paste').html());
    $('.grp-entry tbody').mousemove(grp_mouse_move);
  });
  
  // the titles for manifestations' actions
  $('.manifestation .fg-button-mini').each(function(){
    $(this).attr('title',$.trim($(this).html()));
  });
                  
});

function grp_mouse_move(event)
{
  // down / top
  if ( window.innerHeight - event.pageY < 30 )
  {
    $('.grp-entry tbody').unbind('mousemove');
    $('html').animate({
      scrollTop: ($('html').scrollTop()+150)+'px'
    },{
      duration: 500,
      complete: function() { $('.grp-entry tbody').mousemove(grp_mouse_move); }
    });
  }
  if ( event.pageY < 250 )
  {
    $('.grp-entry tbody').unbind('mousemove');
    $('html').animate({
      scrollTop: ($('html').scrollTop()-150)+'px'
    },{
      duration: 500,
      complete: function() { $('.grp-entry tbody').mousemove(grp_mouse_move); }
    });
  }
  
  // left / right
  if ( window.innerWidth - event.pageX < 100 )
  {
    $('.grp-entry tbody').unbind('mousemove');
    $('html').animate({
      scrollTop: ($('html').scrollLeft()+300)+'px'
    },{
      duration: 500,
      complete: function() { $('.grp-entry tbody').mousemove(grp_mouse_move); }
    });
  }
  if ( event.pageX < 100 && $('html').scrollLeft() > 10 )
  {
    $('.grp-entry tbody').unbind('mousemove');
    $('html').animate({
      scrollTop: ($('html').scrollLeft()-300)+'px'
    },{
      duration: 500,
      complete: function() { $('.grp-entry tbody').mousemove(grp_mouse_move); }
    });
  }
}

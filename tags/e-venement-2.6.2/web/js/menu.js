$(document).ready(function(){
  // removing empty fieldset from jRoller modules
  setTimeout(function(){
    $('.ui-tabs-panel.ui-widget-content').each(function(){
      if ( $(this).children().length == 0 )
      {
        $('.ui-tabs-nav li a[href="#'+$(this).prop('id')+'"]').parent().remove();
        $(this).remove();
      }
    });
  },500);
  
  // getting private script
  src = $('head script[src$="js/menu.js"]').prop('src');
  if ( src != undefined )
    $.getScript(src.replace('js/menu.js','')+'private/menu.js');
  
  // doing a short effect on page unload
  $('a[href]:not([href^="#"]):not([target="_blank"]):not([href^="mailto:"]').click(window_transition);
  $('form:not([target=_blank]').submit(window_transition);
  $('#transition .close').click(function(){ $('#transition, #about').fadeOut('medium'); $('#menu li').removeClass('show'); });
  
  // changing menu
  $('#menu > li > span').mouseenter(function(){
    if ( $('#menu li.show').length > 0 && !$(this).parent().hasClass('show') )
      $(this).click().focus();
  });
  // clicking everywhere but a menu
  $('body').click(function(){
    if ( $('#menu li:hover').length == 0 )
      $('#menu li').removeClass('show');
  });
  // clicking on any menu
  $('#menu > li').click(function(){
    addShow = !$(this).hasClass('show');
    
    $('#menu li').removeClass('show');
    $('#menu li .onit').removeClass('onit');
    $('#menu li .onsub').removeClass('onsub');
    
    if ( addShow )
      $(this).addClass('show').focus();
  });
  // clicking on any submenu
  $('#menu .second > li > a, #menu .third > li > a').click(function(){
    $('#menu .show').removeClass('show');
  });
  // subsubmenu
  $('#menu .second > > a').mouseenter(function(){
    $('#menu .onit').removeClass('onit');
    $('#menu .onsub').removeClass('onsub');
    $(this).focus().parent().addClass('onit');
  })
  .mouseleave(function(){
    if ( $(this).parent().find('.third').length > 0 )
      $(this).parent().removeClass('onit');
  });
  $('#menu .third').mouseenter(function(){
    $(this).focus().closest('li').addClass('onsub');
  })
  .mouseleave(function(){
    $('#menu .onit').removeClass('onit');
    $('#menu .onsub').removeClass('onsub');
  });
  $('#menu .third a').mouseenter(function(){
    $('#menu .third .onit').removeClass('onit');
    $(this).focus().closest('li').addClass('onit');
  })
  .mouseleave(function(){
    $('#menu .third .onit').removeClass('onit');
  });
  
  $('#menu .fancybox').click(function(){
    if ( $('#about').length == 0 )
      $('<iframe src="'+$(this).prop('href')+'" id="about"></iframe>').hide().appendTo('body');
    $('#about').fadeIn('slow');
    return false;
  });
  about_show_contributors();
  
  $(document).keypress(function(e){
    switch ( e.keyCode ) {
    case 27:
      $('#transition .close').click();
      break;
    case 39: /* left-right */
    case 37:
      if ( $('#menu > .show').length > 0 )
      {
        var lis = $('#menu > li');
        var j = e.keyCode == 37 ? -1 : 1;
        
        // expanding a submenu
        if ( $('#menu .onit .third').length > 0 )
        {
          if ( e.keyCode == 39 )
          {
            $('#menu .onit .third a:first').mouseenter();
            return false;
          }
          else
          {
            $('#menu .onsub > a').mouseenter();
            return false;
          }
        }
        
        for ( i = 0 ; i < lis.length ; i++ )
        if ( lis.eq(i).is('.show') )
        {
          var k = i + j;
          if ( k < 0 )
            k = lis.length - 1;
          else if ( k >= lis.length )
            k = 0;
          
          lis.eq(k).click().focus();
          break;
        }
        return false;
      }
      break;
    case 38: /* top-down */
    case 40:
      if ( $('#menu > .show').length > 0 )
      {
        var j = e.keyCode == 38 ? -1 : 1;
        j = j * (e.shiftKey ? 2 : 1);
        
        // expanding a submenu
        if ( $('#menu .onsub .third .onit').length > 0 )
        {
          lis = $('#menu .onsub .third li');
          for ( i = 0 ; i < lis.length ; i++ )
          if ( lis.eq(i).is('.onit') )
          {
            var k = i + j;
            if ( k < 0 )
              k = lis.length - 1;
            else if ( k >= lis.length )
              k = 0;
            
            if ( lis.eq(k).find('a').length == 0 )
              k += j;
            
            lis.eq(k).find('a').mouseenter();
            break;
          }
          return false;
        }
        
        if ( $('#menu > .show .onit').length == 0 )
          $('#menu > .show .second > li:first-child > a').mouseenter();
        else
        {
          lis = $('#menu > .show .second > li');
          
          for ( i = 0 ; i < lis.length ; i++ )
          if ( lis.eq(i).is('.onit') )
          {
            var k = i + j;
            if ( k < 0 )
              k = lis.length - 1;
            else if ( k > lis.length - 1 )
              k = 0;
            
            if ( lis.eq(k).find('a').length == 0 )
              k += j;
            
            lis.eq(k).find('> a').mouseenter();
            break;
          }
        }
        return false;
      }
      break;
    default:
      break;
    } 
  });
});

function about_show_contributors()
{
  $('.ui-widget-about .show-contributors').unbind().click(function(){
    $(this).parent().parent().parent().parent().find('.contributors').fadeToggle('slow');
    return false;
  });
}

function window_transition(speed)
{
  if ( speed == 'undefined' )
    speed = 'medium';
  
  $('#transition').fadeIn(speed);
}

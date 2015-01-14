$(document).ready(function(){
  $('#calendar').unbind().load(load_calendar);
  
  // fullcalendar printing workarounds
  $('head link[media=screen]').prop('media', 'all');
  $('.sf_admin_action_print').click(function(){
    //if ( !confirm($(this).find('a').attr('data-confirm')) )
    //  return false;
    
    $('body').css('width', '26.2cm');
    $('#fullcalendar .fc-resourceName').each(function(){
      $(this).width($(this).width());
    });
    $(window).resize(); // force the calculation of the new width by fullcalendar
    
    // let fullcalendar the time to recalculate the display
    setTimeout(function(){
      window.print();
    },1000);
    
    return false;
  });
  window.onafterprint = function(){
    // let fullcalendar the time to recalculate the display  
    setTimeout(function(){
      if ( window.location.hash == '#debug' )
      {
        $('head link').prop('media', 'all');
        return;
      }
      
      $('body').css('width', '');
      $(window).resize(); // force the calculation of the new width by fullcalendar
    },1000);
  };
  
  // the event filters
  $.get($('.sf_admin_actions_form .event_filters a').prop('href'), function(data){
    // the buttons
    $(data).find('#sf_admin_filters_buttons').appendTo($('.sf_admin_actions_form .event_filters'));
    $('.sf_admin_actions_form .event_filters #sf_admin_filters_buttons #sf_admin_filter_button').click(function(){
      $('.sf_admin_actions_form .event_filters #sf_admin_filter').toggle();
      return false;
    });
    
    // the filters themselves
    var elt = $(data).find('#sf_admin_filter')
      .addClass('ui-widget-content').addClass('ui-corner-all');
    elt.find('form').submit(function(){
      $('.sf_admin_actions_form .event_filters #sf_admin_filters_buttons #sf_admin_filter_button').click();
      $.post($(this).prop('action'), $(this).serialize(), function(){
        $('.sf_admin_actions_form .sf_admin_action_refetch_data a').click();
      });
      return false;
    });
    LI.calendar_autocompletes = [];
    var scripts = elt.find('script');
    scripts.remove();
    elt.appendTo($('.sf_admin_actions_form .event_filters'));
    scripts.each(function(){
      // autocomplete fields
      var js = $(this).text().replace(
        'jQuery(document).ready(function() {',
        'LI.calendar_autocompletes.push(function() {'
      );
      eval(js);
    });
    $.each(LI.calendar_autocompletes, function(key, fct){ fct(); });
  }, 'html');
});


function load_calendar()
{
  window_transition();
  $.get(relative_url_ics_content,function(post){
    // the ics/ical content has been generated in the "post" var
    $.ajax({
      url: $('#calendar').prop('src'),
      type: 'POST',
      dataType: 'html',
      data: { ical: post },
      success: function(data){
        // the calendar graphical representation has been also generated in the "html" var
        $('#calendar').contents().find('body')
          .html($.parseHTML(data))
          .find('meta, title, link, .footer').remove();
        
        //$('#calendar').css('height',$('#calendar').contents().find('html').height());
        $('#calendar').contents().find('a:not([href^=http])').each(function(){
          $(this).prop('href',relative_url_phpicalendar+$(this).prop('href')+'&cal=nocal');
        });
        $('#calendar').contents().find('a:not([href^=http])').click(function(){
          $('#calendar').prop('src',$(this).prop('href'));
          load_calendar();
          return false;
        });
        $('#transition .close').click();
      }
    });
  });
}

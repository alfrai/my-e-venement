$(document).ready(function(){
  $('#calendar').unbind().load(load_calendar);
});


function load_calendar()
{
  window_transition();
  $.get(relative_url_ics_content,function(post){
    // the ics/ical content has been generated in the "post" var
    $.ajax({
      url: $('#calendar').attr('src'),
      type: 'POST',
      dataType: 'html',
      data: { ical: post },
      success: function(data){
        // the calendar graphical representation has been also generated in the "html" var
        $('#calendar').contents().find('body')
          .html(data)
          .find('meta, title, link, .footer').remove();
        $('#calendar').css('height',$('#calendar').contents().find('html').height());
        $('#calendar').contents().find('a:not([href^=http])').each(function(){
          $(this).attr('href',relative_url_phpicalendar+$(this).attr('href')+'&cal=nocal');
        });
        $('#calendar').contents().find('a:not([href^=http])').click(function(){
          $('#calendar').attr('src',$(this).attr('href'));
          load_calendar();
          return false;
        });
        $('#transition').fadeOut('medium');
      }
    });
  });
}

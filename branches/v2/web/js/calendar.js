$(document).ready(function(){
  $('#calendar').unbind().load(load_calendar);
});


function load_calendar()
{
  $.get('http://localhost/e-venement-2/event_dev.php/event/304/calendar',function(post){
    // the ics/ical content has been generated in the "post" var
    $.ajax({
      url: $('#calendar').attr('src'),
      type: 'POST',
      data: { ical: post },
      success: function(data){
        // the calendar graphical representation has been also generated in the "html" var
        $('#calendar').contents().find('body')
          .html(data)
          .find('meta, title, link, .footer').remove();
        $('#calendar').contents().find('a').click(function(){
          $('#calendar').attr('src',relative_url_phpicalendar+$(this).attr('href')+'&cal=nocal');
          load_calendar();
          return false;
        });
      }
    });
  });
}

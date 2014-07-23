  $(document).ready(function(){
    $('#li-direct-access a.fg-button').click(function(){
      // current day by default
      var start = new Date;
      var stop = new Date(str = start.getFullYear()+'/'+(start.getMonth()+1)+'/'+(start.getDate()+1));
      switch ( $(this).attr('href') ) {
      case '#month':
        start = new Date(start.getFullYear()+'/'+(start.getMonth()+1)+'/'+'01');
        stop = new Date(start.getFullYear()+'/'+(start.getMonth()+1)+'/'+'31');
        break;
      case '#week':
        start = new Date(start.getFullYear()+'/'+(start.getMonth()+1)+'/'+(start.getDate()-start.getDay()+1));
        stop = new Date(start.getFullYear()+'/'+(start.getMonth()+1)+'/'+(start.getDate()-start.getDay()+8));
        break;
      }
      $('#sf_admin_filter [name="manifestation_filters[happens_at][from][day]"],   #sf_admin_filter [name="event_filters[dates_range][from][day]"]').val(start.getDate());
      $('#sf_admin_filter [name="manifestation_filters[happens_at][from][month]"], #sf_admin_filter [name="event_filters[dates_range][from][month]"]').val(start.getMonth()+1);
      $('#sf_admin_filter [name="manifestation_filters[happens_at][from][year]"],  #sf_admin_filter [name="event_filters[dates_range][from][year]"]').val(start.getFullYear());
      $('#sf_admin_filter [name="manifestation_filters[happens_at][to][day]"],     #sf_admin_filter [name="event_filters[dates_range][to][day]"]').val(stop.getDate());
      $('#sf_admin_filter [name="manifestation_filters[happens_at][to][month]"],   #sf_admin_filter [name="event_filters[dates_range][to][month]"]').val(stop.getMonth()+1);
      $('#sf_admin_filter [name="manifestation_filters[happens_at][to][year]"],    #sf_admin_filter [name="event_filters[dates_range][to][year]"]').val(stop.getFullYear());
      $('#sf_admin_filter form').submit();
      return false;
    });
  });

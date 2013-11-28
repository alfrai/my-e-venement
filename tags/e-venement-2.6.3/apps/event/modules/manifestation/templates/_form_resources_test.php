<script type="text/javascript"><!--
  // test on resources, to display whether they're free or not
  $(document).ready(function(){
    $('.sf_admin_form select[name="manifestation[location_id]"]').change(function(){
      li_manifestation_check_resource(this);
    }).change();
    $('.sf_admin_form input[name="manifestation[booking_list][]"]').change(function(){
      if ( $(this).is(':checked') )
        li_manifestation_check_resource(this);
      else
        $(this).parent().removeClass('ui-state-error')
          .find('.error.conflict').remove();
    }).change();
    
    // for every change in reservation dates or in blocking state
    $('.sf_admin_form_field_happens_at, .sf_admin_form_field_ends_at, .sf_admin_form_field_reservation_begins_at, .sf_admin_form_field_reservation_ends_at, .sf_admin_form input[name="manifestation[blocking]"]').change(function(){
      $('.sf_admin_form input[name="manifestation[booking_list][]"], .sf_admin_form select[name="manifestation[location_id]"]').change();
    });
  });
  
  function li_manifestation_check_resource(elt = NULL)
  {
    // not a blocking booking
    if ( $('input[name="manifestation[blocking]"]:checked').length == 0 )
    {
      $('.sf_admin_form_field_booking_list li.ui-state-error')
        .removeClass('ui-state-error')
        .find('.error.conflict').remove();
      $('.sf_admin_form_field_location_id').each(function(){
        if ( $(this).find('input[value=""]').length > 0 )
          return;
        $(this).removeClass('ui-state-error')
          .find('.error.conflict').remove();
      });
      return;
    }
    
    var start = li_manifestation_datetime('reservation_begins_at').getTime()/1000;
    var stop  = li_manifestation_datetime('reservation_ends_at').getTime()/1000;
    var location_id = $(elt).val();
    if ( !start || !stop || !location_id )
      return;
    
    $.get('<?php echo url_for('manifestation/list') ?>',{
      start: start,
      end: stop,
      location_id: location_id,
      no_ids: ['<?php echo $manifestation->id ?>'],
      only_blocking: true,
    }, function(data){
      if ( data.length > 0 )
      {
        $(elt).parent().find('.error.conflict').remove();
        for ( i = 0 ; i < data.length ; i++ )
        if ( parseInt(data[i].id)+'' === ''+data[i].id )
        {
          $(elt).parent().append($('<div class="error conflict"><a></a></div>')).find('.error.conflict a')
            .html(data[i].title)
            .prop('href', data[i].hackurl)
            ;
        }
        $(elt).parent().addClass('ui-state-error').addClass('ui-corner-all');
      }
      else
      {
        $(elt).parent().removeClass('ui-state-error')
          .find('.error.conflict').remove();
      }
    }, 'json');
  }
--></script>

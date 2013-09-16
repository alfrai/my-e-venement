<script type="text/javascript"><!--
  // test on resources, to display whether they're free or not
  $(document).ready(function(){
    $('.sf_admin_form select[name="manifestation[location_id]"]').change(function(){
      li_manifestation_check_resource(this);
    }).change();
    $('.sf_admin_form input[name="manifestation[booking_list][]"]').change(function(){
      if ( $(this).is(':checked') )
        li_manifestation_check_resource(this);
    }).change();
    
    // for every change in reservation dates
    $('.sf_admin_form_field_happens_at, .sf_admin_form_field_ends_at, .sf_admin_form_field_reservation_begins_at, .sf_admin_form_field_reservation_ends_at').change(function(){
      $('.sf_admin_form input[name="manifestation[booking_list][]"], .sf_admin_form select[name="manifestation[location_id]"]').change();
    });
  });
  
  function li_manifestation_check_resource(elt)
  {
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
    }, function(data){
      if ( data.length > 0 )
        $(elt).parent().addClass('ui-state-error').addClass('ui-corner-all');
      else
        $(elt).parent().removeClass('ui-state-error');
    }, 'json');
  }
--></script>

<script type="text/javascript"><!--
  // test on resources, to display whether they're free or not
  $(document).ready(function(){
    $('.sf_admin_form select[name="manifestation[location_id]"]').change(function(){
      LI.manifestation_check_resource(this);
    }).change();
    $('.sf_admin_form input[name="manifestation[booking_list][]"]').change(function(){
      if ( $(this).is(':checked') )
        LI.manifestation_check_resource(this);
      else
        $(this).parent().removeClass('ui-state-error')
          .find('.error.conflict').remove();
    }).change();
    
    // for every change in reservation dates or in blocking state
    $('.sf_admin_form_field_happens_at, .sf_admin_form_field_ends_at, .sf_admin_form_field_reservation_begins_at, .sf_admin_form_field_reservation_ends_at, .sf_admin_form input[name="manifestation[blocking]"]').change(function(){
      $('.sf_admin_form input[name="manifestation[booking_list][]"], .sf_admin_form select[name="manifestation[location_id]"]').change();
    });
  });
  
  if ( LI == undefined )
    var LI = {};
  LI.data = {
    url: '<?php echo url_for('manifestation/list') ?>',
    no_ids: ['<?php echo $manifestation->id ?>'],
  };
--></script>

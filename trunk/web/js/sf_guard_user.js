$(document).ready(function(){
  $('input[name="autocomplete_sf_guard_user[contact_id]"]').change(function(){
    if ( !$(this).val() )
      $('input[name="sf_guard_user[contact_id]"]').val('');
  });
});

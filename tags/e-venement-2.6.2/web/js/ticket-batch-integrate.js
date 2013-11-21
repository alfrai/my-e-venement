$(document).ready(function(){
  $('#batch-integrate .sf_admin_form_field_filetype input').change(function(){
    if ( $(this).is('#integrate_filetype_fb:checked') )
      $('#batch-integrate .sf_admin_form_field_translation_prices input[type=text] + input[type=text]')
        .prop('disabled',false)
        .fadeIn();
    else
      $('#batch-integrate .sf_admin_form_field_translation_prices input[type=text] + input[type=text]')
        .prop('disabled',true)
        .fadeOut();
  });
  
  $('.sf_admin_form_field_gauges_list input[type=radio]:first').click();
  $('#batch-integrate .sf_admin_form_field_filetype input:checked').change();
});

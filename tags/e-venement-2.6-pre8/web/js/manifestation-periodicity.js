$(document).ready(function(){
  // when clicking on a text field, the related radio button is selected automatically
  $('#periodicity_behaviour input[type=text], #periodicity_behaviour input.ui-datepicker-trigger').click(function(){
    $(this).parent().find('input[type=radio]').prop('checked',true).change();
  });
  
  // when selecting a radio button, cursor goes directly to the next text field
  $('#periodicity_behaviour input[type=radio]').change(function(){
    $(this).parent().find('input[type=text]:first').focus();
    
    // if "one_occurrence" is selected, then the "repeat every" fields are deactivated
    if ( $(this).val() == 'one_occurrence' )
      $('#periodicity_repeat input').prop('disabled',true);
    else
      $('#periodicity_repeat input').prop('disabled',false);
  }).first().change();
});

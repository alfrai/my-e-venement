$(document).ready(function(){
  // contact
  $('#contact #autocomplete_contact_id').change(function(){ $(this).submit(); });
  $('#contact').unbind().submit(function(){
    $.post($(this).attr('action'),$(this).serialize(),function(data){
      $('#contact').html($(data).find('#contact').html());
    });
    return false;
  });
  
  // professional
  
  
});

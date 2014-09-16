$(document).ready(function(){
  $('form #control_checkpoint_id').change(function(){
    $('#control_ticket_id').focus();
  });
});

$(document).ready(function(){
  $('input.ac_input').change(function(){
    if ( !$(this).val() )
      $(this).parent().find('input[type=hidden]').val('');
  });
});

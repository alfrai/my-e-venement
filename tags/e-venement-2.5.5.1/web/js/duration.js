$(document).ready(function(){
  $('.sf_admin_form_field_duration input[type=text]').each(function(){
    $(this).val(Math.floor(parseInt($(this).val(),10)/3600)+':'+('0'+Math.floor(parseInt($(this).val(),10)%3600/60)).slice(-2));
  });
});

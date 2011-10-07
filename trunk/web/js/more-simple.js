$(document).ready(function(){
  $('#more .members > div').click(function(){
    $(this).parent().find('ul').slideToggle('slow');
  }).click();
});

$(document).ready(function(){
  // the price_gauges submission
  $('.sf_admin_form .sf_admin_form_field_gauges_prices').niceScroll();
  $('.sf_admin_form .sf_admin_form_field_gauges_prices [data-submit-url] input[type=text]').keydown(function(e){
    if ( e.which != 13 )
      return true;

    var data = {};
    $(this).parent().find('input').each(function(){
      data[$(this).prop('name')] = $(this).val(); 
    });
    
    $.get($(this).closest('[data-submit-url]').attr('data-submit-url'), data, function(json){
      $.each(json,function(type, msg){
        LI.alert(msg, type);
      });
    });
    
    return false;
  });
});

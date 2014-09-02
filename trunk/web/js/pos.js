$(document).ready(function(){
  $('.sf_admin_form_field_prices_list input').each(function(){
    $(this).insertAfter($(this).parent().find('label'));
  });
  $('.sf_admin_form_field_prices_list .help').html(
    $('.sf_admin_form_field_prices_list .help').html()+
    $('.sf_admin_form_field_prices .help').text()
  );
  $('.sf_admin_form_field_prices input + input').each(function(){ // not very solid, but don't have something better yet
    $('<span class="value"></span>')
      //.append($(this).closest('table').find('label'))
      .append($(this).closest('table').find('input'))
      .appendTo($('.sf_admin_form_field_prices_list input[value="'+$(this).val()+'"]').closest('li'));
    ;
  });
  
  $('.sf_admin_form_field_declinations table + input[type=hidden]').each(function(){
    if ( isNaN(parseInt($(this).val(),10)) || parseInt($(this).val(),10) <= 0 )
      return;
    var label = $(this).closest('table').closest('tr').find('> th');
    var span = $('<span></span>').text('pouet');
    label.text(label.text()+' ').append(span);
  });
  
  // resolving a graphical conflict
  setTimeout(function(){
    $('.sf_admin_form_field_prices_list ul').removeAttr('class').removeAttr('role').addClass('checklist');
  }, 200);
});

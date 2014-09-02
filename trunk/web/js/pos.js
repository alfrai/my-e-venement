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
    var del = $('<a></a>')
      .prop('href', $(this).closest('form').find('.sf_admin_form_field_declination_del').prop('href'))
      .attr('data-id', $(this).val())
      .prop('title', $.trim($(this).closest('form').find('.sf_admin_form_field_declination_del').text()))
      .text($(this).closest('form').find('.sf_admin_form_field_declination_del').text())
      .addClass('fg-button').addClass('ui-state-default').addClass('fg-button-icon-left').addClass('ui-priority-secondary')
      .prepend($('<span></span>').addClass('ui-icon').addClass('ui-icon-trash'))
      .mouseenter(function(){ $(this).addClass('ui-state-hover') })
      .mouseleave(function(){ $(this).removeClass('ui-state-hover') })
      .click(function(){
        var elt = this;
        $('#transition').show();
        $.get($(this).prop('href'), { declination_id: $(this).attr('data-id') }, function(){
          $('#transition .close').click();
          $(elt).closest('tr').fadeOut(function(){ $(this).remove(); });
        });
        return false;
      })
    ;
    label.text(label.text()+' ').append(del);
  });
  
  // resolving a graphical conflict
  setTimeout(function(){
    $('.sf_admin_form_field_prices_list ul').removeAttr('class').removeAttr('role').addClass('checklist');
  }, 200);
});

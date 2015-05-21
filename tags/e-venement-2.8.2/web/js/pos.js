// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};
if ( LI.posAfterRendering == undefined )
  LI.posAfterRendering = [];

$(document).ready(function(){
  // reorganizing the prices list
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
  
  // playing w/ declinations to make it more ergonomic
  $('.sf_admin_form_field_declinations table + input[type=hidden]').each(function(){
    
    // existing declinations
    var label = $(this).closest('table').closest('tr').find('> th');
    var del = $('<a></a>')
      .prop('href', $(this).closest('form').find('.sf_admin_form_field_declination_del').prop('href'))
      .attr('data-id', $(this).val())
      .prop('title', $.trim($(this).closest('form').find('.sf_admin_form_field_declination_del').text()))
      .prop('target', '_blank')
      .text($(this).closest('form').find('.sf_admin_form_field_declination_del').text())
      .addClass('fg-button').addClass('ui-state-default').addClass('fg-button-icon-left').addClass('ui-priority-secondary').addClass('li-delete')
      .prepend($('<span></span>').addClass('ui-icon').addClass('ui-icon-trash'))
      .mouseenter(function(){ $(this).addClass('ui-state-hover'); })
      .mouseleave(function(){ $(this).removeClass('ui-state-hover'); })
      .click(function(){
        var elt = this;
        console.error('click');
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
  
  // rotating labels
  $('#sf_fieldset_declinations textarea, #sf_fieldset_declinations table table, #sf_fieldset_declinations .widget > table > tbody > tr > th')
    .closest('tr').find('> th').each(function(){
      var txt = $(this).clone().children().remove().end().text();
      $('<div></div>').addClass('rotated')
        .text(txt)
        .append($(this).find('> *'))
        .appendTo($(this).remove('> *'))
      ;
    });
  $('#sf_fieldset_declinations textarea').closest('table').find('input')
    .click(function(){
      if ( !$(this).closest('.sf_admin_form_row').find('> label a').hasClass('see-all') )
      {
        var elt = this;
        $('#sf_fieldset_declinations textarea').closest('tr').each(function(){
          if ( !$(this).closest('table').is($(elt).closest('table')) ) $(this).fadeOut();
        });
      }
      $(this).closest('table').find('textarea').closest('tr').fadeIn();
    })
    .closest('.sf_admin_form_row').find('> label').append(
      $('<a></a>').prop('href', '#').text($('#display-declination-msg .on').text()).css('float', 'right').click(function(){
        if ( $(this).hasClass('see-all') )
        {
          $(this).removeClass('see-all').text($('#display-declination-msg .on').text());
          $('#sf_fieldset_declinations textarea').closest('tr').fadeOut();
        }
        else
        {
          $(this).addClass('see-all').text($('#display-declination-msg .off').text());
          $('#sf_fieldset_declinations textarea').closest('tr').fadeIn();
        }
        return false;
      })
    );
  
  // resolving a graphical conflict
  setTimeout(function(){
    $('.sf_admin_form_field_prices_list ul').removeAttr('class').removeAttr('role').addClass('checklist');
  }, 200);
  
  // after rendering actions
  $.each(LI.posAfterRendering, function(id, fct){
    fct();
  });
});

LI.posToggleNewDeclination = function(elt, parent, show)
{
  if ( show )
  {
    $(parent).closest('.li-new-declination').removeClass('li-hidden');
    $(parent).find('.fg-button').remove();
    elt.hide().appendTo($(parent).removeClass('li-show')).fadeIn();
    $(parent).closest('.li-new-declination').find('th .li-delete').unbind('click').click(function(){
      LI.posToggleNewDeclination(elt, parent, false);
      return false;
    });
  }
  else
  {
    var declination = $(elt).clone(true);
    $(parent).find('*').remove();
    $(parent).closest('.li-new-declination').addClass('li-hidden');
  
    // the "show" button
    $('<button></button>')
      .addClass('fg-button').addClass('ui-state-default').addClass('fg-button-icon-left')
      .mouseenter(function(){ $(this).addClass('ui-state-hover'); })
      .mouseleave(function(){ $(this).removeClass('ui-state-hover'); })
      .html('<span class="ui-icon ui-icon-circle-plus"></span>')
      .appendTo($(parent))
      .click(function(){
        LI.posToggleNewDeclination(declination, parent, true)
        return false;
      })
    ;
  }
}

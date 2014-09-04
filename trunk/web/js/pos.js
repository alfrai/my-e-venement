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
      .text($(this).closest('form').find('.sf_admin_form_field_declination_del').text())
      .addClass('fg-button').addClass('ui-state-default').addClass('fg-button-icon-left').addClass('ui-priority-secondary').addClass('li-delete')
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
  
    // new declination
    if ( isNaN(parseInt($(this).val(),10)) || parseInt($(this).val(),10) <= 0 )
    {
      // init
      var elt = $(this).closest('table');
      var parent = $(elt).parent();
      $(elt).closest('tr').addClass('li-new-declination');
      elt.find('table tr + tr td > input[hidden]').appendTo(elt.find('tr:first-child td'));
      elt.find('table tr + tr').remove();
      
      // the "hide" button
      parent.closest('.li-new-declination').find('th .li-delete').unbind('click').click(function(){
        LI.posToggleNewDeclination(elt, parent, false);
        return false;
      }).click();
    }
  });
  
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

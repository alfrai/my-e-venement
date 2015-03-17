if ( LI == undefined )
  LI = {};

$(document).ready(function(){
  LI.form_list();
});

LI.form_list = function()
{
  $('.sf_admin_form .sf_admin_form_list.ajax').each(function(){
    var widget = $(this).get(0);
    
    $(this).load(widget.url+' .sf_admin_list',function(){
      LI.form_list_change(widget);
      LI.form_list_new();
      LI.form_list_actions(widget);
      LI.form_list_more(widget);
      LI.form_on_quit();
    });
    
  });
}

LI.form_list_more = function(widget)
{
  if ( widget.functions != undefined )
  for ( i = 0 ; i < widget.functions.length ; i++ )
  if ( typeof(widget.functions[i]) == 'function' )
    widget.functions[i]();
}

LI.form_list_actions = function(widget)
{
  // delete
  $('.sf_admin_form_list .sf_admin_action_delete a').unbind().removeAttr('onclick').click(function(){
    r = confirm($('#more .i18n.are-you-sure').html());
    if ( r )
    $.post($(this).prop('href'),{
      _csrf_token:  $('.sf_admin_form .sf_admin_form_list.ajax').find('._delete_csrf_token').html(),
      sf_method:    'delete',
    },function(data){
      LI.form_list();
    });
    return false;
  });
  
  // update
  $('.sf_admin_form .sf_admin_form_list.ajax form:not(.sf_admin_new)').unbind().submit(function(){
    var form = $(this);
    // apply changes on similar fields forms
    $(this).find('input:not([type=hidden])').each(function(){
      $('form[action="'+form.prop('action')+'"] [name="'+$(this).prop('name')+'"][type=hidden]')
        .val(!$(this).is('input[type=checkbox]') ? $(this).val() : $(this).is(':checked') ? 1 : 0);
    });
    
    // make-up
    $(this).find('input[type=text]').prependTo($(this));
    $(this).find('*:not(input)').remove();
    
    // post request
    $.post($(this).prop('action'),$(this).serialize(),function(data){
      data = $.parseHTML(data);
      
      var object_id = $(data).find('form').prop('action').match(/\/(\d+)(\.\w+){0,1}$/)[1];
      var input = $('.sf_admin_form .sf_admin_form_list.ajax .object-'+object_id+' input[type=text]');
      var form = input.closest('form');
      var widget = form.closest('.ajax').get(0);
      
      form.find('.label, .sf_admin_flashes').remove();
      if ( form.find('.sf_admin_form_field_value > *').length <= 1 )
      {
        form.find(widget.field+' > input').prependTo(form);
        form.find(widget.field).remove();
        $(data).find('.sf_admin_flashes').prependTo(form);
        setTimeout(function(){
          form.find('.sf_admin_flashes').fadeOut('medium',function(){
            $(this).remove();
          });
        },3000);
      }
      LI.form_list_change(widget);
    });
    return false;
  });
  
  // pager
  LI.form_list_pager(widget);
}

LI.form_list_pager = function(widget)
{
  $('#sf_admin_pager .button a').unbind().click(function(){
    elt = $(this).closest('.sf_admin_form_list');
    $.get($(this).prop('href'),function(data){
      elt.html($($.parseHTML(data)).find('.sf_admin_list'));
      LI.form_list_new(widget);
      LI.form_list_actions(widget);
      LI.form_list_more(widget);
    });
    return false;
  });
}

LI.form_list_change = function(widget)
{
  LI.form_list_more(widget);
  $('.sf_admin_form .sf_admin_form_list.ajax form input[type=text], .sf_admin_form .sf_admin_form_list.ajax form input[type=checkbox]').change(function(){
    $(this).closest('form').submit();
    var input = this;
    setTimeout(function(){
      $(input).get(0).defaultValue = $(input).val();
    },1500);
  });
}

LI.form_on_quit = function()
{
  window.onbeforeunload = function(){
    var count = 0;
    
    $('form form input[type=text]').each(function(){
      if ( $(this).get(0).defaultValue !== $(this).val() )
        count++;
    });
    
    if ( count > 0 )
    if ( msg = $('#form_prices').get(0).wait_msg )
      alert(msg);
  };
}

LI.form_list_new = function()
{
  // new records
  $('.sf_admin_form .sf_admin_form_list.ajax .sf_admin_new select').unbind().change(function(){
    $(this).parent().submit();
  });
  
  $('.sf_admin_form .sf_admin_form_list.ajax .sf_admin_new form').unbind().submit(function(){
    $.post($(this).prop('action'),$(this).serialize(),function(data){
      LI.form_list();
    });
    return false;
  });
}

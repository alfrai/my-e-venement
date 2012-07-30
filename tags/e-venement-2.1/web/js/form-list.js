$(document).ready(function(){
  form_list();
});

function form_list()
{
  $('.sf_admin_form .sf_admin_form_list.ajax').each(function(){
    var widget = $(this).get(0);
  
    $(this).load(widget.url+' .sf_admin_list',function(){
      form_list_focusout();
      form_list_new();
      form_list_actions();
    });
  });
}

function form_list_actions()
{
  $('.sf_admin_form_list .sf_admin_action_delete a').unbind().removeAttr('onclick').click(function(){
    r = confirm('Are you sure?');
    if ( r )
    $.post($(this).attr('href'),{
      _csrf_token:  $('.sf_admin_form .sf_admin_form_list.ajax').find('._delete_csrf_token').html(),
      sf_method:    'delete',
    },function(data){
      form_list();
    });
    return false;
  });
  
  $('.sf_admin_form .sf_admin_form_list.ajax form:not(.sf_admin_new)').unbind().submit(function(){
    form = $(this);
    widget = $(this).closest('.ajax').get(0);
    
    // apply changes on similar fields forms
    form.find('input:not([type=hidden])').each(function(){
      $('form[action="'+form.attr('action')+'"] [name="'+$(this).attr('name')+'"][type=hidden]')
        .val(!$(this).is('input[type=checkbox]') ? $(this).val() : $(this).is(':checked') ? 1 : 0);
    });
    
    // make-up
    form.find('input[type=text]').prependTo(form);
    form.find('*:not(input)').remove();
    
    // post request
    $.post($(this).attr('action'),$(this).serialize(),function(data){
      form.find('input[type=text]').replaceWith($(data).find(widget.field));
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
      form_list_focusout();
    });
    return false;
  });
  
  // pager
  form_list_pager();
}

function form_list_pager()
{
  $('#sf_admin_pager .button a').unbind().click(function(){
    elt = $(this).closest('.sf_admin_form_list');
    $.get($(this).attr('href'),function(data){
      elt.html($(data).find('.sf_admin_list'));
      form_list_new();
      form_list_actions();
    });
    return false;
  });
}

function form_list_focusout()
{
  $('.sf_admin_form .sf_admin_form_list.ajax form input[type=text], .sf_admin_form .sf_admin_form_list.ajax form input[type=checkbox]').unbind().focusout(function(){
    $(this).parent().submit();
  });
}

function form_list_new()
{
  $('.sf_admin_form .sf_admin_form_list.ajax .sf_admin_new select').unbind().change(function(){
    $(this).parent().submit();
  });
  
  $('.sf_admin_form .sf_admin_form_list.ajax .sf_admin_new form').unbind().submit(function(){
    $.post($(this).attr('action'),$(this).serialize(),function(data){
      form_list();
    });
    return false;
  });
}

$(document).ready(function(){
  $('.sf_admin_form_row .checkbox_list').each(function(){
    if ( $(this).find('input[type=checkbox]').length > 1 )
    {
      $('<input type="checkbox" name="all" value="all" />')
        .click(function(){
          $(this).closest('.sf_admin_form_row').find('.checkbox_list input[type=checkbox]').click();
          return false;
        })
        .prependTo($(this).closest('.sf_admin_form_row').find('> label, > .label > label'))
      ;
    }
  });
});

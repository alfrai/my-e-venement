$(document).ready(function(){
  $('.sf_admin_form .form_prices').load('http://localhost/e-venement-2/event_dev.php/price_manifestation/894/batchEdit .sf_admin_list',function(){
    manifestation_price_focusout();
    $('.sf_admin_form .form_prices form').submit(function(){
      // make-up
      form = $(this);
      form.find('[name="price_manifestation[value]"]').prependTo(form);
      form.find('*:not(input)').remove();
      
      // post request
      $.post($(this).attr('action'),$(this).serialize(),function(data){
        form.find('[name="price_manifestation[value]"]').replaceWith($(data).find('.sf_admin_form_field_value'));
        form.find('.label, .sf_admin_flashes').remove();
        if ( form.find('.sf_admin_form_field_value > *').length <= 1 )
        {
          form.find('.sf_admin_form_field_value > input').prependTo(form);
          form.find('.sf_admin_form_field_value').remove();
          $(data).find('.sf_admin_flashes').prependTo(form);
          setTimeout(function(){
            form.find('.sf_admin_flashes').fadeOut('medium',function(){
              $(this).remove();
            });
          },3000);
        }
        manifestation_price_focusout();
      });
      return false;
    });
  });
  
  $('select[name="manifestation[event_id]"], select[name="manifestation[location_id]"]').each(function(){
    if ( $(this).find('option[selected=selected]').length > 0 )
    {
      $(this).attr('disabled','disabled');
      elt = $('<input type="hidden" name="'+$(this).attr('name')+'" value="'+$(this).find('option:selected').attr('value')+'" />');
      elt.insertAfter($(this));
      
      if ( $(this).val() )
      {
        form = $('.sf_admin_form form:first').parents().find('form');
        if ( $(this).attr('name') == 'manifestation[event_id]' )
          tmp = 'event';
        else
          tmp = 'location';
          
        args = form.attr('action',form.attr('action').indexOf('?') != -1
          ? form.attr('action')+'&'+tmp+'='+$(this).val()
          : form.attr('action')+'?'+tmp+'='+$(this).val());
      }
    }
  });
});

function manifestation_price_focusout()
{
    $('.sf_admin_form .form_prices form input[type=text]').unbind().focusout(function(){
      $(this).parent().submit();
    });
}

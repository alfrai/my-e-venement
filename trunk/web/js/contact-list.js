$(document).ready(function(){
  // adding the possibility to edit in the list itself the records
  $('.sf_admin_row .sf_admin_text').dblclick(function(){
    $('form.specialized-form:not(.submitting)').submit();
    
    fieldname = $(this).prop('class').replace(/sf_admin_list_td_(\w+)/g,"$1").replace(/sf_admin_text/g,'').trim();
    id = $(this).closest('.sf_admin_row').find('[name="ids[]"]').val();
    
    $(this).load(window.location+'/'+id+'/getSpecializedForm?field='+fieldname+' #nothing',function(data){
      if ( $(data).find('.specialized-form input[type=text]').length > 0 )
      {
        width = $(this).innerWidth()-10+'px';
        $(this).html($(data).find('.specialized-form'));
        $(this).find('input[type=text]:first').css('width',width);
        $(this).find('input[type=text]:first').focus(function(){ if(this.value == this.defaultValue) this.select(); });
        $(this).find('input[type=text]:first').focus();
        $(this).find('.specialized-form').submit(function(){
          $(this).addClass('submitting');
          $.post($(this).prop('action'), $(this).serialize(), function(data){
            $('.specialized-form.submitting').each(function(){
              $(this).closest('.sf_admin_text').html($(this).find('input[type=text]:first').val());
            });
          });
          return false;
        });
      }
    });
  });
  // submit all specialized forms when submitting any form on the page
  $('form:not(.specialized-form').submit(function(){
    $('form.specialized-form:not(.submitting)').submit();
  });
  
  // making emails clickable except when filling down the list through AJAX
  $('.sf_admin_list_td_email').each(function(){
    if ( $(this).html().trim() )
      $(this).html('<a title="'+$(this).html().trim()+'" href="mailto:'+$(this).html().trim()+'">'+$(this).html()+'</a>');
  });
  // adding titles to emails when already clickables
  $('.sf_admin_list_td_list_emails a').each(function(){
    $(this).prop('title',$(this).closest('li').prop('title')+': '+$(this).html().trim());
  });
  
  // this permits to get a year-only widget without an error and without big modification in generic code
  $('#sf_admin_filter .from_year select:first-child, #sf_admin_filter .to_year select:first-child').change(function(){
    $(this).parent().find('select + select option:selected').removeAttr('selected');
    if ( $(this).val() == '' )
      $(this).parent().find('select + select option:first-child').prop('selected','selected');
    else
      $(this).parent().find('select + select option:first-child + option').prop('selected','selected');
  });
  
  setTimeout(contact_batch_change,1000); // setTimeout is a hack...
});

function contact_batch_change()
{
  $('.ui-selectmenu-menu-dropdown a[role=option]').click(function(){
    if ( $(this).html() == $('select[name=batch_action] option[value=batchAddToGroup]').html() )
    {
      $('.sf_admin_batch_actions_choice input[type=submit]').before(
        $('#contact_filters_not_groups_list').clone(true)
          .prop('name','groups[]')
          .prop('id','batch_action_group')
          .addClass('ui-corner-all')
      );
      $('.sf_admin_batch_actions_choice input[type=submit]').after('<div style="clear: both"></div>');
    }
    else
    {
      $('#batch_action_group').fadeOut('medium');
    }
  });
}

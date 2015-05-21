// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  LI.contact_list_behaviour();
  
  // submit all specialized forms when submitting any form on the page
  $('form:not(.specialized-form').submit(function(){
    $('form.specialized-form:not(.submitting)').submit();
  });
  
  // for the scrolling feature
  if ( window.list_scroll_end == undefined )
    window.list_scroll_end = new Array();
  window.list_scroll_end[window.list_scroll_end.length] = LI.contact_list_behaviour;
  if ( window.integrated_search_end == undefined )
    window.integrated_search_end = new Array();
  window.integrated_search_end[window.integrated_search_end.length] = LI.contact_list_behaviour;
});

LI.contact_list_behaviour = function()
{
  // making emails clickable except when filling down the list through AJAX
  $('.sf_admin_list_td_email').each(function(){
    if ( $(this).text().trim() )
      $(this).html('<a title="'+$(this).text().trim()+'" href="mailto:'+$(this).text().trim()+'">'+$(this).text()+'</a>');
  });
  // adding titles to emails when already clickables
  $('.sf_admin_list_td_list_emails a').each(function(){
    $(this).prop('title',$(this).closest('li').prop('title')+': '+$(this).text().trim());
  });
  
  // this permits to get a year-only widget without an error and without big modification in generic code
  $('#sf_admin_filter .from_year select:first-child, #sf_admin_filter .to_year select:first-child').change(function(){
    $(this).parent().find('select + select option:selected').removeAttr('selected');
    if ( $(this).val() == '' )
      $(this).parent().find('select + select option:first-child').prop('selected','selected');
    else
      $(this).parent().find('select + select option:first-child + option').prop('selected','selected');
  });
  
  setTimeout(LI.contact_batch_change,1000); // setTimeout is a hack... useless w/ the TDP design
}

// useless w/ the TDP design
LI.contact_batch_change = function()
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

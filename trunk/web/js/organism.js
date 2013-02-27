$(document).ready(function(){
  setTimeout(batch_change,1000); // setTimeout is a hack...
});

function batch_change()
{
  $('.ui-selectmenu-menu-dropdown a[role=option]').click(function(){
    if ( $(this).html() == $('select[name=batch_action] option[value=batchAddToGroup]').html() )
    {
      $('.sf_admin_batch_actions_choice input[type=submit]').before(
        $('#organism_filters_contacts_groups').clone(true)
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

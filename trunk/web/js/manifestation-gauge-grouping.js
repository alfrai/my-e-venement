if ( LI == undefined )
  LI = {};
if ( LI.manifestationFormWorkspaces == undefined )
  LI.manifestationFormWorkspaces = [];

LI.manifestationFormWorkspaces.push(function(){
  $('#sf_fieldset_workspaces .sf_admin_list_td_join input').change(function(){
    if ( !$(this).prop('checked') )
    {
      $(this).closest('tr').insertBefore($(this).closest('tr').siblings('.sf_admin_new'));
      $(this).closest('tr').find('td').css('display', '');
      $(this).closest('tr').find('.sf_admin_list_td_join_name input').val('').change();
    }
    
    var trs = $(this).closest('tbody').find('.sf_admin_list_td_join input:checked').closest('tr');
    trs.find('.sf_admin_list_td_join_name').prop('rowspan', 0);
    var parent = trs.find('.sf_admin_list_td_join_name input:not([value=""]):first').closest('tr');
    if ( parent.length == 0 )
      parent = trs.first();
    trs.prependTo($(this).closest('tbody')).find('td:first-child + td')
      .css('display', 'none').addClass('merged');
    parent.prependTo($(this).closest('tbody')).find('td:first-child + td')
      .css('display', '').removeClass('merged').prop('rowspan', trs.length);
    trs.find('.sf_admin_list_td_join_name input').unbind().change(function(){
      if ( !$(this).closest('tr').find('.sf_admin_list_td_join input:checked').length > 0 )
        trs.find('.sf_admin_list_td_join_name input').val($(this).val());
      
      // RECORD THE GROUP NAME IN THE DB
    });
  });
});

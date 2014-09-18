if ( LI == undefined )
  LI = {};

LI.manifestationGroupGauges = function(){
  // initialization
  $('#sf_fieldset_workspaces .sf_admin_list_td_join_name input[type=text]').unbind('focusout');
  $('#sf_fieldset_workspaces .sf_admin_list_td_join_name').prop('rowspan', '1').show();
  $('#sf_fieldset_workspaces .sf_admin_list_td_join_name select').remove();
  $('#sf_fieldset_workspaces .sf_admin_list_td_unjoin *').hide();
  $('#sf_fieldset_workspaces tbody tr').addClass('alone');
  
  // analysing the content
  var cats = {};
  $('#sf_fieldset_workspaces .sf_admin_list_td_join_name input[type=text]').each(function(){
    var cat;
    if ( cat = $.trim($(this).val()) )
    {
      if ( cats[cat] == undefined )
        cats[cat] = [];
      cats[cat].push(this);
    }
  });
  
  // get out of a group
  $('#sf_fieldset_workspaces .sf_admin_list_td_unjoin button').unbind('click').click(function(){
    $(this).closest('tr').find('.sf_admin_list_td_join_name select, .sf_admin_list_td_join_name input[type=text]').val('');
    $(this).closest('tr').find('.sf_admin_list_td_join_name input[type=text]').change();
    LI.manifestationGroupGauges();
    return false;
  });
  
  // the choices
  $('<select></select>').prop('name', 'goup_names').hide().append($('<option></option>'))
    .appendTo($('#sf_fieldset_workspaces .sf_admin_list_td_join_name'))
    .change(function(){
      $(this).closest('.sf_admin_list_td_join_name').find('input[type=text]')
        .val($(this).val()).change().focusout();
    });
  
  // grouping gauges
  $.each(cats, function(grp, elt){
    // select
    $('#sf_fieldset_workspaces .alone .sf_admin_list_td_join_name select').show()
      .append($('<option></option>').val(grp).text(grp));
    
    // rowspan & prepend
    $(elt).closest('tr').prependTo($(elt).closest('tbody')).removeClass('alone');
    $(elt).closest('tr').find('.sf_admin_list_td_join_name').hide();  // all the lines
    $(elt).closest('tr').first().find('.sf_admin_list_td_join_name')  // first line
      .prop('rowspan', elt.length).show();
    $(elt).closest('tr').find('.sf_admin_list_td_join_name select').hide();
    $(elt).closest('tr').find('.sf_admin_list_td_unjoin *').show();
    $(elt).closest('tr').find('.sf_admin_list_td_join_name input[type=text]').focusout(function(){
      // first change()
      $(elt).closest('tr').find('.sf_admin_list_td_join_name input[type=text]').val($(this).val()).change();
    });
  });
  
  // get in a group
  $('#sf_fieldset_workspaces .sf_admin_list_td_join_name input[type=text]').focusout(function(){
    // last change()
    LI.manifestationGroupGauges();
  });
}

if ( LI.manifestationFormWorkspaces == undefined )
  LI.manifestationFormWorkspaces = [];
LI.manifestationFormWorkspaces.push(LI.manifestationGroupGauges);

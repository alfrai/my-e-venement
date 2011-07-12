$(document).ready(function(){
  // this does some make-up for filter form
  title = '<div class="ui-widget ui-widget-content ui-corner-all"><div class="ui-widget-header ui-corner-all fg-toolbar"><h2>Titre</h2></div>';
  elt = $(''+
    '<tr><td class="fieldset" colspan="2">'+
    '  <div class=" ui-widget ui-widget-content ui-corner-all">'+
    '    <table>'+
    '      <thead><tr><td colspan="2">'+title+'</td></tr></thead>'+
    '      <tbody></tbody>'+
    '    </table>'+
    '  </div>'+
    '</td></tr>');
  
  elt = elt.clone(true);
  elt.find('tbody').html('')
    .append($('#sf_admin_filter .sf_admin_filter_field_description'))
    .append($('#sf_admin_filter .sf_admin_filter_field_YOB'))
    .append($('#sf_admin_filter .sf_admin_filter_field_family_contact'))
    .append($('#sf_admin_filter .sf_admin_filter_field_npai'))
    .append($('#sf_admin_filter .sf_admin_filter_field_has_address'))
    .append($('#sf_admin_filter .sf_admin_filter_field_has_email'))
    .append($('#sf_admin_filter .sf_admin_filter_field_emails_list'));
  elt.find('h2').html('Additionnel');
  elt.prependTo('#sf_admin_filter > form > table > tbody');
  
  elt = elt.clone(true);
  elt.find('tbody').html('')
    .append($('#sf_admin_filter .sf_admin_filter_field_event_categories_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_meta_events_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_events_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_prices_list'));
  elt.find('h2').html('Evenementiel');
  elt.prependTo('#sf_admin_filter > form > table > tbody');
  
  elt = elt.clone(true);
  elt.find('tbody').html('')
    .append($('#sf_admin_filter .sf_admin_filter_field_groups_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_not_groups_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_organism_id'))
    .append($('#sf_admin_filter .sf_admin_filter_field_organism_category_id'))
    .append($('#sf_admin_filter .sf_admin_filter_field_professional_type_id'));
  elt.find('h2').html('Relations');
  elt.prependTo('#sf_admin_filter > form > table > tbody');
  
  elt = elt.clone(true);
  elt.find('tbody').html('')
    .append($('#sf_admin_filter .sf_admin_filter_field_firstname'))
    .append($('#sf_admin_filter .sf_admin_filter_field_name'))
    .append($('#sf_admin_filter .sf_admin_filter_field_postalcode'))
    .append($('#sf_admin_filter .sf_admin_filter_field_city'))
    .append($('#sf_admin_filter .sf_admin_filter_field_country'));
  elt.find('h2').html('Personnel');
  elt.prependTo('#sf_admin_filter > form > table > tbody');

  // this permits to get a year-only widget without an error and without big modification in generic code
  $('#sf_admin_filter .from_year select:first-child, #sf_admin_filter .to_year select:first-child').change(function(){
    $(this).parent().find('select + select option:selected').removeAttr('selected');
    if ( $(this).val() == '' )
      $(this).parent().find('select + select option:first-child').attr('selected','selected');
    else
      $(this).parent().find('select + select option:first-child + option').attr('selected','selected');
  });
});

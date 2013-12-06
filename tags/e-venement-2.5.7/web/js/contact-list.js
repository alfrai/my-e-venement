$(document).ready(function(){
  // adding the possibility to edit in the list itself the records
  $('.sf_admin_row .sf_admin_text').dblclick(function(){
    $('.specialized-form').submit();
    
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
    $('.specialized-form').submit();
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
    .append($('#sf_admin_filter .sf_admin_filter_field_email_newsletter'))
    .append($('#sf_admin_filter .sf_admin_filter_field_family_contact'))
    .append($('#sf_admin_filter .sf_admin_filter_field_npai'))
    .append($('#sf_admin_filter .sf_admin_filter_field_has_address'))
    .append($('#sf_admin_filter .sf_admin_filter_field_has_email'))
    .append($('#sf_admin_filter .sf_admin_filter_field_has_category'))
    .append($('#sf_admin_filter .sf_admin_filter_field_emails_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_updated_at'));
  elt.find('h2').html('Additionnel');
  elt.prependTo('#sf_admin_filter > form > table > tbody');
  
  if ( $('#sf_admin_filter .sf_admin_filter_field_control_created_at').length > 0 )
  {
    elt = elt.clone(true);
    elt.find('tbody').html('')
      .append($('#sf_admin_filter .sf_admin_filter_field_control_created_at'))
      .append($('#sf_admin_filter .sf_admin_filter_field_control_manifestation_id'))
      .append($('#sf_admin_filter .sf_admin_filter_field_control_checkpoint_id'));
    elt.find('h2').html('Gestion des flux');
    elt.prependTo('#sf_admin_filter > form > table > tbody');
  }
  
  if ( $('#sf_admin_filter .sf_admin_filter_field_member_cards').length > 0 )
  {
    elt = elt.clone(true);
    title = $('#sf_admin_filter .sf_admin_filter_field_member_cards label').html();
    elt.find('tbody').html('')
      .append($('#sf_admin_filter .sf_admin_filter_field_member_cards'))
      .append($('#sf_admin_filter .sf_admin_filter_field_member_cards_expire_at'));
    elt.find('h2').html(title);
    elt.prependTo('#sf_admin_filter > form > table > tbody');
  }
  
  elt = elt.clone(true);
  elt.find('tbody').html('')
    .append($('#sf_admin_filter .sf_admin_filter_field_event_categories_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_meta_events_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_events_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_prices_list'))
    .append($('#sf_admin_filter .sf_admin_filter_field_event_archives'))
    .append($('#sf_admin_filter .sf_admin_filter_field_tickets_amount_min'))
    .append($('#sf_admin_filter .sf_admin_filter_field_tickets_amount_max'));
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
    .append($('#sf_admin_filter .sf_admin_filter_field_title'))
    .append($('#sf_admin_filter .sf_admin_filter_field_firstname'))
    .append($('#sf_admin_filter .sf_admin_filter_field_name'))
    .append($('#sf_admin_filter .sf_admin_filter_field_postalcode'))
    .append($('#sf_admin_filter .sf_admin_filter_field_city'))
    .append($('#sf_admin_filter .sf_admin_filter_field_region_id'))
    .append($('#sf_admin_filter .sf_admin_filter_field_country'))
    .append($('#sf_admin_filter .sf_admin_filter_field_email'));
  elt.find('h2').html('Personnel');
  elt.prependTo('#sf_admin_filter > form > table > tbody');

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

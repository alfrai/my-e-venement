// disabling direct validation / links
function contact_ajax_form(id, add, hide)
{
  if ( typeof(add) != 'boolean' )  add = false;
  if ( typeof(hide) != 'boolean' ) hide = true;
  $(id+' #sf_admin_form_tab_menu').addClass('sf_admin_form_tab_menu').removeAttr('id');

  // show/hide subelements
  $(id).parent().find('h2').parent().unbind().click(function(){
    $(this).parent().find('div.sf_admin_form').toggle();
  });
  if ( hide )
    $(id).parent().find('h2').click();
  
  // style and action scripts
  $(id+' form div.label').each(function(){
    $(this).contents().insertBefore($(this));
    $(this).remove();
  });
  arr = ['contact','organism'];
  for ( i in arr )
  {
    jQuery(id+' input[name="autocomplete_professional['+arr[i]+'_id]"]')
    .autocomplete('/e-venement-2/rp_dev.php/'+arr[i]+'/ajax/action', jQuery.extend({}, {
      dataType: 'json',
      parse:    function(data) {
        var parsed = [];
        for (key in data) {
          parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
        }
        return parsed;
      }
    }, { }))
    .result(function(event, data) { jQuery(id+' input[name="professional['+arr[i]+'_id]"]').val(data[1]); });
  }
  
  $(id+' form a').unbind().click(function(){ return false; });
  
  // supprimer
  $(id+' form a[onclick]').unbind().removeAttr('onclick').click(function(){
    if ( !confirm("Êtes-vous sûr de vouloir supprimer cette fonction ?") )
      return false;
    
    var elt = $(this).parent().parent().parent().parent().parent().parent();
    elt.fadeOut('slow');
    $.ajax({
      url: $(this).attr('href'),
      type: 'POST',
      data: {
        sf_method: 'delete',
        _csrf_token: elt.find('._delete_csrf_token').html(),
      },
      success: function(data) {
        elt.remove();
        $('#more').prepend('<div class="sf_admin_flashes ui-widget ui-widget-content ui-corner-all"><div class="notice ui-state-highlight">Fonction supprimée.</div></div>');
        info = $('#more .sf_admin_flashes:first');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
      error: function(data,error) {
        elt.fadeIn('slow');
        $('#more').prepend('<div class="sf_admin_flashes ui-widget ui-widget-content ui-corner-all"><div class="error ui-state-error">Impossible de supprimer la fonction... ('+error+')</div>');
        info = $('#more .sf_admin_flashes:first');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
    });
    return false;
  });
  
  // update / add
  $(id+' form').submit(function(){
    url = $(this).attr('action');
    $.post(url,$(this).serialize(),function(data){
      if ( add )
      {
        $('#more #professional-new').toggle();
        id = 'professional-r'+$('#more .professional.recent').length;
        $(str = '<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all professional recent"><div class="ui-widget-header ui-corner-all fg-toolbar "><h2></h2></div><div id="'+id+'" class="sf_admin_form"></div></div>')
          .insertAfter($('#more .professional.new'));
        id = '#more #'+id;
      }
      $(id)
        .html   ( $(data).find('form') )
        .prepend( $(data).find('.error, .notice') );
      if ( add )
      {
        $('#more .professional.recent:last h2')
          .html('Organisme: <span>'+$(id+' input[name="autocomplete_professional[organism_id]"]').val()+'</span>');
        $('#more .professional.new form').get(0).reset();
      }
      info = $(id+' .error, '+id+' .notice').first();
      info.hide().fadeIn('slow');
      setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      contact_ajax_form(id,false,false);
    });
    return false;
  });
}

function contact_load_professionals(i)
{
  if ( typeof(professionals) != "undefined" )
  if ( professionals[i] )
  {
    $.get(professionals[i],function(data){
      $('#more #professional-'+i).html( $(data).find('form') );
      contact_ajax_form('#more #professional-'+i);
      contact_load_professionals(i+1);
    });
  }
}

$(document).ready(function(){

  if ( $('#more').length <= 0 )
    return false;

  // existing professionals
  contact_load_professionals(0);
  
  // new professional
  if ( typeof(professional_new) != "undefined" )
  $.get(professional_new,function(data){
    $('#more #professional-new').html( $(data).find('form') );
    contact_ajax_form('#more #professional-new',true);
    $('#more #professional-new input[name="professional[contact_id]"]').val($('#contact_id').val());
  });
  
});

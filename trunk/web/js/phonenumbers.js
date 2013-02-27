function phonenumbers_add(data,beforethis)
{
  data = $.parseHTML(data);
  
  li = $('<li class="phonenumber phonenumber-'+$(data).find(pnid).val()+'"></li>')
    .append($(data).find('.sf_admin_form form').clone(true));
  
  if ( $(data).find(pnid).val() != '' )
  {
    // existing
    if ( beforethis == undefined || $(beforethis).length == 0 )
      $('#content .form_phonenumbers').append(li);
    else
      li.insertBefore(beforethis);
  }
  else
  {
    // new
    $('#content .form_phonenumbers').append(li);
  }
  
  $('.phonenumber-'+$(data).find(pnid).val()+' input[name="autocomplete_contact_phonenumber[name]"], .phonenumber-'+$(data).find(pnid).val()+' input[name="autocomplete_organism_phonenumber[name]"]')
    .change(function(){
      // a hack for getting the selected value from autocompleter
      setTimeout(function(){
        elt = $('.phonenumber-'+$(data).find(pnid).val()+' input[name="autocomplete_contact_phonenumber[name]"], .phonenumber-'+$(data).find(pnid).val()+' input[name="autocomplete_organism_phonenumber[name]"]');
        elt.parent().parent()
          .find('input[name="contact_phonenumber[name]"], input[name="organism_phonenumber[name]"]')
          .val(elt.val());
      },150);
    })
    .autocomplete(phonetype_ajax, jQuery.extend({}, {
      dataType: 'json',
      parse:    function(data) {
        var parsed = [];
        for (key in data) {
          parsed[parsed.length] = { data: [ data[key], data[key] ], value: data[key], result: data[key] };
        }
        return parsed;
      }
    }, { }))
    .result(function(event, data) {
      $('.phonenumber-'+$(data).find(pnid).val()+' input[name="contact_phonenumber[name]"], .phonenumber-'+$(data).find(pnid).val()+' input[name="organism_phonenumber[name]"]')
        .val(data[1]);
    });
  
  // contact[id] | organism[id]
  $('#content .form_phonenumbers input[name="contact_phonenumber[contact_id]"]').val($('#contact_id').val());
  $('#content .form_phonenumbers input[name="organism_phonenumber[organism_id]"]').val($('#organism_id').val());
  
  // delete
  $('#content .form_phonenumbers .phonenumber-'+$(data).find(pnid).val()+' form a[onclick]').unbind().removeAttr('onclick').click(function(){
    if ( !confirm("Êtes-vous sûr de vouloir supprimer ce numéro de téléphone ?") )
      return false;
    
    var elt = $(this).parent().parent().parent().parent();
    elt.fadeOut('slow');
    
    $.ajax({
      url: $(this).prop('href'),
      type: 'POST',
      data: {
        sf_method: 'delete',
        _csrf_token: elt.find('._delete_csrf_token').html(),
      },
      success: function(data) {
        elt.remove();
        $('#sf_fieldset_phonenumbers').prepend('<div class="sf_admin_flashes ui-widget ui-widget-content ui-corner-all"><div class="notice ui-state-highlight">Numéro supprimé.</div></div>');
        info = $('#sf_fieldset_phonenumbers .sf_admin_flashes:first');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
      error: function(data,error) {
        elt.fadeIn('slow');
        $('#sf_fieldset_phonenumbers').prepend('<div class="sf_admin_flashes ui-widget ui-widget-content ui-corner-all"><div class="error ui-state-error">Impossible de supprimer le numéro... ('+error+')</div></div>');
        info = $('#sf_fieldset_phonenumbers .sf_admin_flashes:first');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
    });
    
    return false;
  });
    
  // form validations for updates
  $('#content .form_phonenumbers .phonenumber-'+$(data).find(pnid).val()+' form').unbind().submit(function(){
    $.post($(this).prop('action'),$(this).serialize(),function(data){
      if ( $('#content .form_phonenumbers .phonenumber-'+$($.parseHTML(data)).find(pnid).val()).length <= 0 )
      {
        // new object
        $('#content .form_phonenumbers .phonenumber- form').get(0).reset();
        phonenumbers_add(data,$('#content .form_phonenumbers .phonenumber-'));
      }
      
      $('#content .form_phonenumbers .phonenumber-'+$($.parseHTML(data)).find(pnid).val()).prepend(
        $('<div class="sf_admin_flashes ui-widget"></div>').html($($.parseHTML(data)).find('.notice, .error').addClass('ui-state-highlight').addClass('ui-corner-all'))
      );
      
      var info = $('#content .form_phonenumbers .phonenumber-'+$($.parseHTML(data)).find(pnid).val()+' .sf_admin_flashes')
        .hide().fadeIn('slow',function(){
          setTimeout(function(){ info.fadeOut('slow',function(){info.remove()}); },5000);
        });
    });
    return false;
  });
}

$(document).ready(function(){
  for ( i in phonenumbers )
  $.get(phonenumbers[i],phonenumbers_add);
});

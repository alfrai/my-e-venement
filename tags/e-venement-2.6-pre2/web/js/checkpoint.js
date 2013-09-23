function event_checkpoints_autocompleter()
{
  jQuery(' input[name="autocomplete_checkpoint[organism_id]"]')
  .autocomplete(event_organism_ajax_url, jQuery.extend({}, {
    dataType: 'json',
    parse:    function(data) {
      var parsed = [];
      for (key in data) {
        parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
      }
      return parsed;
    }
  }, { }))
  .result(function(event, data) { jQuery('input[name="checkpoint[organism_id]"]').val(data[1]); });
}
function event_checkpoints_list_load(url)
{
  url = url ? url : event_checkpoint_list_url;
  $('.sf_admin_form .checkpoint_list').load(url+' .sf_admin_list',function(){
    $('.sf_admin_form .checkpoint_list tfoot input').remove();
    $('.sf_admin_form .checkpoint_list tfoot a').click(function(){
      event_checkpoints_list_load($(this).prop('href'));
      return false;
    });
    $('.sf_admin_form .checkpoint_list tbody a').click(function(){
      $('.sf_admin_form .checkpoint_new').load($(this).prop('href')+'/edit .sf_admin_form form',event_checkpoints_new_load);
      return false;
    });
    
    // delete
    $('.sf_admin_form .sf_admin_list .sf_admin_action_delete a').unbind().removeAttr('onclick').click(function(){
      if ( !confirm("Êtes-vous sûr de vouloir supprimer cet élément ?") )
        return false;
      
      $.ajax({
        url: $(this).prop('href'),
        type: 'POST',
        data: {
          sf_method: 'delete',
          _csrf_token: $('.checkpoints ._delete_csrf_token').html(),
        },
        success: function(data) {
          $('.sf_admin_form .checkpoint_new').html($($.parseHTML(data)).find('.sf_admin_form form'));
          event_checkpoints_new_load(data);
          event_checkpoints_list_load();
        }
      });
      
      return false;
    });
  });
}

function event_checkpoints_new_load(data)
{
  data = $.parseHTML(data);
  
  event_checkpoints_autocompleter();
  $('.sf_admin_form .checkpoint_new').prepend($(data).find('.sf_admin_flashes'));
  setTimeout(function(){
    $('.sf_admin_flashes .notice').fadeOut(function(){ $(this).remove(); });
  },3000);
  $('.sf_admin_form .checkpoint_new').prepend($(data).find('.ui-widget-header'));
  $('.sf_admin_form .checkpoint_new h1').replaceWith('<h2>'+$('.sf_admin_form .checkpoint_new h1').html()+'</h2>');
  
  $('.sf_admin_form select[name="checkpoint[event_id]"] option[value='+$('input[type=hidden][name="event[id]"]').val()+']').prop('selected','selected');
  if ( $('.sf_admin_form select[name="checkpoint[event_id]"] option:selected').length > 0 )
  if ( $('.sf_admin_form select[name="checkpoint[event_id]"]').val() )
  {
    $('<input type="hidden" name="checkpoint[event_id]" value="'+$('.sf_admin_form select[name="checkpoint[event_id]"]').val()+'"/>').insertAfter($('.sf_admin_form select[name="checkpoint[event_id]"]'));
    $('.sf_admin_form select[name="checkpoint[event_id]"]').prop('disabled','disabled');
  }
  
  $('.sf_admin_form .checkpoint_new .sf_admin_actions_block:first').remove();
  $('.sf_admin_form .checkpoint_new').each(function(){
    $(this).find('.sf_admin_action_list, .sf_admin_action_save_and_add').remove();
    button = $(this).find('.sf_admin_action_save_and_add button').clone(true);
    button.prepend($(this).find('.sf_admin_action_save_and_add').html());
    button.find('button').remove();
    $(this).find('.sf_admin_action_save_and_add').html('');
    $(this).find('.sf_admin_action_save_and_add').append(button);
  }); 
  $('.sf_admin_form .checkpoint_new form').submit(function(){
    $.post($(this).prop('action'),$(this).serialize()+'&_save_and_add=on',function(data){
      $('.sf_admin_form .checkpoint_new').html($($.parseHTML(data)).find('.sf_admin_form form'));
      event_checkpoints_new_load(data);
      event_checkpoints_list_load();
    });
    return false;
  });
}

$(document).ready(function(){
  $('.sf_admin_form .checkpoint_new').load(event_checkpoint_new_url+' .sf_admin_form form',
    event_checkpoints_new_load);
  event_checkpoints_list_load();
});


// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

LI.seatedPlanRanksInBulk = function(){
  $('#transition').show();
  
  // the ajax request
  var data = {};
  $('#sf_fieldset_ranks input[name^=auto_ranks]').each(function(){
    data[$(this).prop('name')] = $(this).val();
  });
  if ( !$('#sf_fieldset_ranks [name="auto_ranks[contiguous]"]').prop('checked') )
    delete data['auto_ranks[contiguous]'];
  $.post($('#sf_fieldset_ranks .ranks_explanation a').prop('href'), data, function(data){
    $('#transition .close').click();
  });
}

LI.seatedPlanLinksInBulk = function(elt){
  if ( $(elt).attr('data-no-msg') !== 'no-msg' )
    $('#transition').show();
  
  // the ajax request
  var data = {};
  var url = false;
  var text = '';
  var tmp = '';
  switch ( $(elt).prop('name') ) {
  
  // CLEAR
  case 'auto_links[clear]':
    data[$(elt).prop('name')] = $(elt).val();
    url = $('#sf_fieldset_seat_links .links_links .links_clear').prop('href');
    text = $('#sf_fieldset_seat_links .links_links .links_clear').text();
    break;
  
  // BUILD
  case 'auto_links[contiguous]':
  case 'auto_links[format]':
  case 'auto_links[format_submit]':
    if ( $('#sf_fieldset_seat_links [name="auto_links[contiguous]"]').prop('checked') )
      data['auto_links[contiguous]'] = $('#sf_fieldset_seat_links [name="auto_links[contiguous]"]').val();
    data['auto_links[format]'] = $('#sf_fieldset_seat_links [name="auto_links[format]"]').val();
    url = $('#sf_fieldset_seat_links .links_links .links_build').prop('href');
    text = $('#sf_fieldset_seat_links .links_links .links_build').text();
    break;
  
  // ADD
  case 'auto_links[exceptions_to_add]':
  case 'auto_links[exceptions_to_add_submit]':
    tmp = 'to_add';
  // REMOVE
  case 'auto_links[exceptions_to_remove]':
  case 'auto_links[exceptions_to_remove_submit]':
    if ( !tmp )
      tmp = 'to_remove';
    data['auto_links[exceptions_'+tmp+']'] = $('#sf_fieldset_seat_links [name="auto_links[exceptions_'+tmp+']"]').val();
    url = $('#sf_fieldset_seat_links .links_links .links_exceptions_'+tmp+'').prop('href');
    text = $('#sf_fieldset_seat_links .links_links .links_exceptions_'+tmp+'').text();
  break;
  
  }
  
  if ( url )
  $.get(url, data, function(data){
    if ( typeof(data) == 'object' && data.qty != undefined )
      text = text.replace('%%qty%%', data.qty)
    if ( $(elt).attr('data-no-msg') !== 'no-msg' )
      LI.alert(text, 'success');
    $('#transition .close').click();
    $(elt).removeAttr('data-no-msg');
  });
}

$(document).ready(function(){
  // catch every form validation to avoid global validation, ranks
  $('#sf_fieldset_ranks input[name^=auto_ranks]').keydown(function(e){
    if ( e.which == 13 )
    {
      LI.seatedPlanRanksInBulk();
      return false;
    }
  });
  $('#sf_fieldset_ranks button, #sf_fieldset_ranks input[type=submit]').click(function(){
    LI.seatedPlanRanksInBulk();
    return false;
  });
  
  // catch every form validation to avoid global validation, links
  $('#sf_fieldset_seat_links input[name^=auto_links]').keydown(function(e){
    if ( e.which == 13 )
    {
      LI.seatedPlanLinksInBulk(this);
      return false;
    }
  });
  $('#sf_fieldset_seat_links button, #sf_fieldset_seat_links input[type=submit]').click(function(){
    LI.seatedPlanLinksInBulk(this);
    return false;
  });
});

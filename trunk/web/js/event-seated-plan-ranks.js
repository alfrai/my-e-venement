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
  $.post($('#sf_fieldset_ranks .ranks_explanation a').prop('href'), data, function(data){
    $('#transition .close').click();
  });
}

$(document).ready(function(){
  // catch every form validation to avoid global validation
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
});

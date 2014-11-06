$(document).ready(function(){
  LI.pubNamedTicketsInitialization();
  
  $('#categories form').submit(function(){
    $.ajax({
      type: $(this).prop('method'),
      url:  $(this).prop('action'),
      data: $(this).serialize(),
      success: function(json){
        if ( json.error && json.error.message )
          LI.alert(json.error.message, 'error');
        if ( json.success && json.success.message )
          LI.alert(json.success.message, 'success');
        LI.pubNamedTicketsInitialization();
      }
    });
    return false;
  });
  
  // remove the "loading..." message after a while
  setTimeout(function(){
    $('#plans-loading').remove();
  },10000);
  
  // remove empty selects
  $('#categories select').each(function(){
    if ( $(this).find('option').length == 0 )
      $(this).closest('li').remove();
  });
  
  // drag-scroll from any device for seated-plans
  $('#plans .gauge').overscroll();
  
  // modifying quantities in categories
  $('#categories .qty a').click(function(){
    var newval = parseInt($(this).parent().find('input').val(),10) + parseInt($(this).attr('data-val'),10);
    if ( newval > parseInt($(this).parent().find('input').attr('data-max-value'),10) )
      newval = parseInt($(this).parent().find('input').attr('data-max-value'),10);
    $(this).parent().find('input').val(newval > 0 ? newval : 1);
    return false;
  });
  $('#categories .qty input').change(function(){
    if ( !$(this).val() )
      $(this).val(1);
    if ( parseInt($(this).val(),10) > parseInt($(this).parent().find('input').attr('data-max-value'),10) )
      $(this).val(parseInt($(this).parent().find('input').attr('data-max-value'),10));
  });
  
  // the tabs...
  $('#container .tab h4').click(function(){
    $('#container .tab:not(.hidden)').addClass('hidden');
    $(this).closest('.tab').removeClass('hidden');
  });
  if ( LI.isMobile.any() )
    $('#container .tab + .tab h4').click();
});

// the height of the #container
if ( LI.seatedPlanImageLoaded == undefined )
  LI.seatedPlanImageLoaded = [];
LI.seatedPlanImageLoaded.push(function(){
  $('#container').height($('#plans').height()+15);
});
  

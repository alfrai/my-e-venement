  // the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

// display a flash for a limited time
LI.alert = function(msg, type = 'notice', time = 4000)
{
  var icons = {
    success: 'ui-icon-circle-check',
    notice:  'ui-icon-info',
    error:   'ui-icon-alert',
  }
  var flash = '<div class="%%type%% ui-state-highlight ui-corner-all"><span class="ui-icon %%icon%% floatleft"></span>&nbsp;%%msg%%</div>';
  
  $('.sf_admin_flashes').append($(flash.replace('%%msg%%',msg).replace('%%type%%',type).replace('%%icon%%', icons[type])).hide().css('position', 'absolute').fadeIn('slow'));
  setTimeout(function(){
    $('.sf_admin_flashes > *').fadeOut(function(){ $(this).remove(); })
  },time);
}


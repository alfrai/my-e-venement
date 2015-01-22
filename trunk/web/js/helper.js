// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

LI.pad_num = function (num, digits){return(1e15+num+"").slice(-digits)}

// CURRENCY STYLE ("fr" / "en")
LI.currency_style = function(value)
{
  if ( typeof(value) != 'string' )
    return 'fr';
  
  return isNaN(parseFloat(value.replace(',','.')))
    ? 'en'
    : 'fr'
  ;
}

LI.get_currency = function(value)
{
  if ( typeof(value) != 'string' )
    return '€';
  return value.replace(/[\d\.,\s]+/g, '').replace('&nbsp;','');
}

LI.clear_currency = function(value)
{
  if ( typeof(value) != 'string' )
    return value;
  return parseFloat(value.replace(',','.').replace(/[^\d^\.^\s]+/g, ''));
}

// THE CURRENCY
LI.format_currency = function(value, nbsp, fr_style, currency)
{
  if ( nbsp  == undefined ) nbsp  = true;
  if ( fr_style == undefined ) fr_style = true;
  if ( currency == undefined ) currency = '€';
  if ( typeof(value) == 'string' ) value = parseFloat(value);
  if ( !value ) value = 0;

  var r = $('.currency:first').length > 0
    ? $('.currency:first').html()
    : (fr_style ? '%d '+currency : currency+'%d');
  value = r.replace('%d',value.toFixed(2));

  if ( nbsp  ) value = value.replace(' ','&nbsp;');
  if ( fr_style ) value = value.replace('.',',');

  return value;
}

// display a flash for a limited time
LI.alert = function(msg, type, time)
{
  if ( time == undefined )
    time = 4000;
  if ( type == undefined )
    type = 'notice';
  
  var icons = {
    success: 'ui-icon-circle-check',
    notice:  'ui-icon-info',
    error:   'ui-icon-alert',
  }
  
  var flash = $('<div class="%%type%% ui-state-%%type%% ui-state-highlight ui-corner-all"><span class="ui-icon %%icon%% floatleft"></span>&nbsp;%%msg%%</div>'
    .replace('%%msg%%',msg)
    .replace('%%type%%',type).replace('%%type%%',type)
    .replace('%%icon%%',icons[type]))
    .hide()
    .appendTo($('.sf_admin_flashes').css('position', 'absolute').css('z-index', 10))
    .fadeIn('slow');
  setTimeout(function(){
    flash.fadeOut(function(){ $(this).remove(); })
  },time);
}

LI.isMobile = {
  Android: function() {
    return navigator.userAgent.match(/Android/i);
  },
  BlackBerry: function() {
    return navigator.userAgent.match(/BlackBerry/i);
  },
  iOS: function() {
    return navigator.userAgent.match(/iPhone|iPad|iPod/i);
  },
  Opera: function() {
    return navigator.userAgent.match(/Opera Mini/i);
  },
  Windows: function() {
    return navigator.userAgent.match(/IEMobile/i);
  },
  any: function() {
    return (LI.isMobile.Android() || LI.isMobile.BlackBerry() || LI.isMobile.iOS() || LI.isMobile.Opera() || LI.isMobile.Windows());
  },
  test: function() {
    return true;
  }
};

LI.ifMediaCaptureSupported = function(go, nogo)
{
  var fGetUserMedia =
  (
    navigator.getUserMedia ||
    navigator.webkitGetUserMedia ||
    navigator.mozGetUserMedia ||
    navigator.oGetUserMedia ||
    navigator.msieGetUserMedia ||
    false
  );
  fGetUserMedia.call( navigator, { video: true }, function(){ go() }, function(){ if ( nogo != undefined ) nogo(); } );
}

LI.OFC = {
  init: function(obj) { this.OFC = $(obj); return this; },
  OFC: null,
  name: "Open Flash Charts 2",
  version: function() { return this.OFC[0].get_version(); },
  rasterize: function (dst) { $(dst).replaceWith(this.image()); return this; },
  image: function() { return "<img src='data:image/png;base64," + this.binary() + "' />"; },
  binary: function() { return this.OFC[0].get_img_binary(); },
  popup: function() { window.open('data:image/png;base64,'+this.binary()); return this; }
}

// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

if ( LI.pubCartReady == undefined )
  LI.pubCartReady = [];

LI.pubCartReady.push(function(){
  $('#cart-widget .timer .time').each(function(){
    LI.pubTimer(this);
  });
});

LI.pubTimeout = function()
{
  window.location = $('#ariane .cart a:first').prop('href');
}

LI.pubTimer = function(elt, day, delay)
{
  var d = new Date;
  if ( day == undefined )
    day = d.getDate();
  if ( delay == undefined )
    delay = 1000;
  
  var time = $(elt).text().split(':');
  if ( time.length != 3 )
  {
    LI.pubTimeout();
    return;
  }
  
  d.setHours(parseInt(time[0],10));
  d.setMinutes(parseInt(time[1],10));
  d.setSeconds(parseInt(time[2],10)-1);
  
  if ( d.getDate() == day )
  {
    // timeout
    if ( d.getHours() == 0 && d.getMinutes() == 0 && d.getSeconds() == 0 )
    {
      LI.pubTimeout();
    }
    
    $(elt).text(LI.pad_num(d.getHours(),2)+':'+LI.pad_num(d.getMinutes(),2)+':'+LI.pad_num(d.getSeconds(),2));
    setTimeout(function(){
      LI.pubTimer(elt, day, delay);
    }, delay);
    return;
  }
}

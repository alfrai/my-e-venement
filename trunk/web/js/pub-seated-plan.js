// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  // init data
  LI.pubInitTicketsRequest();
  
  // activate a gauge
  $('.gauge.full-seating').click(function(){
    if ( $(this).is('.active') )
      return;
    
    $('.gauge.full-seating').removeClass('active');
    $(this).addClass('active');
    if ( $(this).find('.picture.seated-plan').is('.done') )
      return;
    LI.seatedPlanInitialization($(this));
  });
  var hash = window.location.hash.split('#');
  
  $('.gauge.full-seating'+(hash[1] ? '[data-gauge-id='+hash[1]+']' : ':first')).click();
  $('.gauge.full-seating .blank').each(function(){
    $(this).height($(this).closest('.gauge.full-seating').height());
  });
  
  // checking for orphans before submitting data
  $('form.adding-tickets').submit(function(){
    LI.pubCheckOrphansVisually($('#ajax-pre-submit').prop('href'), undefined, function(){
      $('form.adding-tickets')
        .unbind('submit').delay(10)             // the delay is a hack ... or it does not submit !?
        .find('[type=submit]:first').click();
    });
    return false;
  });
  
  // checking orphans if asked in the URL at when loading
  var hash = window.location.hash.split('#');
  if ( hash[1] && $.inArray('orphans', hash) != -1 )
  LI.pubAfterRenderingSeats['pubReadyOrphans'] = { one_shot: true, options: { gauge_id: hash[1] }, exec: function(options){
    LI.pubCheckOrphansVisually($('#ajax-pre-submit').prop('href'), options.gauge_id);
  }}
  
  // on changing quantities
  $('.prices .seats .seat[data-seat-id=""], .prices .seats .seat:not([data-seat-id])').each(function(){
    $(this).prependTo($(this).closest('.seats')); // ordering the priorities between tickets
  });
  $('.prices [data-price-id] .quantity select').each(function(){
    var val;
    $(this)
      .focusin(function(){ val = $(this).val(); })
      .focusout(function(){
        if ( val !== $(this).val() )
        {
          var seats = [];
          
          // less tickets
          for ( i = 0 ; i < val - $(this).val() ; i++ )
          {
            // preparing the DB
            seats.push({
              action: 'del',
              ticket_id: $(this).closest('[data-price-id], .seating.in-progress').find('.seat:first').attr('data-ticket-id'),
              gauge_id: $(this).closest('[data-gauge-id]').attr('data-gauge-id'),
            });
            $(this).closest('[data-price-id], .seating.in-progress').find('.seat:first').remove();
          }
          
          // more tickets
          for ( i = 0 ; i < $(this).val() - val ; i++ )
          {
            // preparing the DB
            seats.push({
              action: 'add',
              price_id: $(this).closest('[data-price-id]').attr('data-price-id'),
              gauge_id: $(this).closest('[data-gauge-id]').attr('data-gauge-id')
            });
          }
          
          // in the DB
          if ( seats.length > 0 )
          LI.pubInitTicketsRequest({ tickets: seats });
        }
      })
    ;
  });
});

LI.pubInitTicketsData = function(json){
  $('.prices .quantity select').val(0).change();
  $('.prices .seating.in-progress .quantity').text('-');
  $('.prices .seats *').remove();
  
  // commenting out to avoid "blinking" seats
  //$('.seated-plan.picture .seat.ordered.in-progress')
  //  .removeClass('ordered').removeClass('in-progress')
  //  .removeAttr('data-ticket-id').removeAttr('data-price-id').removeAttr('data-gauge-id');
  
  $('.prices tbody .extra-taxes').text('').attr('data-value', 0);
  $.each(json.tickets, function(key, ticket){
    var line = ticket.price_id
      ? $('#gauge-'+ticket.gauge_id+' .prices [data-price-id='+ticket.price_id+']')
      : $('#gauge-'+ticket.gauge_id+' .prices .seating.in-progress')
    ;
    
    // seats / tickets
    $('<span></span>')
      .addClass('seat').addClass('seat-'+ticket.seat_name)
      .attr('data-ticket-id', ticket.ticket_id)
      .attr('data-seat-id', ticket.seat_id)
      .text(ticket.seat_name)
      .appendTo(line.find('.seats').append(' '))
    ;
    
    // extra taxes
    line.find('.extra-taxes').each(function(){
      var val;
      $(this).html(LI.format_currency(val = parseFloat($(this).attr('data-value'))+ticket['extra-taxes']));
      $(this).attr('data-value', val);
    });
    
    // on the seated plan
    $('#gauge-'+ticket.gauge_id+' .seated-plan.picture .seat[data-id='+ticket.seat_id+']')
      .addClass('ordered').addClass('in-progress')
      .attr('data-ticket-id', ticket.ticket_id)
      .attr('data-price-id', ticket.price_id)
      .attr('data-gauge-id', ticket.gauge_id)
    ;
  });
  
  // quantities
  $('.prices [data-price-id], .prices .seating.in-progress').each(function(){
    // quantity
    if ( $(this).find('.quantity select').length > 0 )
      $(this).find('.quantity select')
        .val($(this).find('.seats .seat').length)
        .change()
      ;
    else // WIPs
      $(this).find('.quantity')
        .text($(this).find('.seats .seat').length)
      ;
  });
  
  // orphans
  if ( json.orphans )
  $.each(json.orphans, function(gid, gauge){
  $.each(gauge, function(key, orphan){
    LI.pubShowOrphansOnPlan(orphan);
  }); });

  LI.pubNamedTicketsInitialization();
}

LI.pubInitTicketsRequest = function(seats){
  if ( typeof(seats) != 'object' )
    seats = {};
  
  $.get($('#ajax-init-data').prop('href'), seats, function(json){
    if (!( json.success && json.success.data && json.success.data.tickets ))
    {
      LI.alert('An error occurred', 'error');
      return;
    }
    if ( json.success.message )
      LI.alert(json.success.message, 'success');
    LI.pubInitTicketsData(json.success.data);
  });
  
}

LI.pubAfterRenderingSeats = {};

// a trick specific to "pub" to execute a function only once after rendering the seated plan
if ( LI.seatedPlanInitializationFunctions == undefined )
  LI.seatedPlanInitializationFunctions = [];
LI.seatedPlanInitializationFunctions.push(function(){
  $.each(LI.pubAfterRenderingSeats, function(key, infos){
    if ( typeof(infos.exec) != 'function' )
    {
      if ( typeof(infos) == 'function' )
        infos();
      return;
    }
    
    infos.exec(infos.options);
    if ( infos.one_shot == true )
      delete LI.pubAfterRenderingSeats[key];
  });
  
  // debugging lazy/stupid IE calculation on SVG's height
  setTimeout(function(){
    $('.seated-plan img').each(function(){
      $(this).height($(this).clone().addClass('hack-to-delete').appendTo('#footer').height());
    });
    $('#footer img.hack-to-delete').remove();
  },3000);
});
  
LI.pubAfterRenderingSeats['pubSeatedPlanInitMain'] = function(){
  $('.seated-plan .seat.txt').unbind('contextmenu').unbind('click').click(function(){
    var seat = this;
    if ( $(seat).is('.ordered.in-progress') )   // removing a seat
    {
      $.get($('#ajax-init-data').prop('href'), { tickets: [{
        ticket_id: $(seat).attr('data-ticket-id'),
        action: 'del'
      }]}, function(json){
        if ( json.error && json.error.message )
          LI.alert(json.error.message, 'error');
        
        if ( !json.success )
          return;
        
        // on the plan
        $(seat).parent().find('.seat-'+$(seat).attr('data-id'))
          .removeClass('in-progress')
          .removeClass('ordered');
        
        // message
        if ( json.success.message )
          LI.alert(json.success.message, 'success');
        
        // in the list
        LI.pubInitTicketsData(json.success.data);
      });
    }
    else  // adding a seat
    {
      $.get($('#ajax-init-data').prop('href'), { tickets: [{
        seat_id: $(seat).attr('data-id'),
        gauge_id: $(seat).closest('[data-gauge-id]').attr('data-gauge-id'),
        action: 'add'
      }]}, function(json){
        if ( json.error && json.error.message )
          LI.alert(json.error.message, 'error');
        
        // removing seats already booked in the meantime
        if ( json.error.seats_to_remove != undefined )
        $.each(json.error.seats_to_remove, function(i, seat_id){
          $(seat).parent().find('.seat-'+seat_id).remove();
        });
        
        if ( !json.success )
          return;
        
        // on the plan
        $(seat).parent().find('.seat-'+$(seat).attr('data-id'))
          .addClass('in-progress')
          .addClass('ordered')
        ;
        
        // message
        if ( json.success.message )
          LI.alert(json.success.message, 'success');
        
        // in the list
        LI.pubInitTicketsData(json.success.data);
      });
    }
  });
}

LI.pubCheckOrphansVisually = function(url, gauge_id, fct)
{
  var data = {};
  if ( gauge_id )
    data.gauge_id = gauge_id;
  
  // get data
  $.get(url, data, function(json){
    if ( json.success && json.success.orphans && json.success.orphans.length == 0 )
    {
      // no orphan, gooooo
      if ( typeof(fct) == 'function' )
         fct();
    }
    else
    {
      // refresh the seated plan first, if needed
      LI.pubAfterRenderingSeats['LI.pubCheckOrphansVisually'] = { one_shot: true, options: { fct: fct, json: json }, exec: function(options)
      {
        var json = options.json;
        var orphans = 0;
        
        // display textual informations
        LI.alert(json.error ? json.error.message : json.success.message, json.error ? 'error' : 'success');
        
        if ( json.success )
        {
          // display on the plan
          $.each(json.success.orphans, function(key, gauge){
          $.each(gauge, function(key, orphan){
            orphans++;
            console.log('Orphan detected on seat '+orphan.seat_name);
            LI.pubShowOrphansOnPlan(orphan)
          }); });
        }
        
        // then do ...
        if ( typeof(options.fct) == 'function' && orphans == 0 )
        {
          console.log('No orphan detected, go forward to the cart.');
          options.fct();
        }
      }}
    }
  });
}

LI.pubShowOrphansOnPlan = function(orphan)
{
  // visual
  var interval = setInterval(function(){
    var oelt = $('#plans .seats-url[data-gauge-id='+orphan.gauge_id+']').closest('.seated-plan')
      .find('.seat[data-id='+orphan.seat_id+']');
    if ( oelt.length == 0 )
      return;
    oelt.addClass('printed').addClass('blink');
    ($('#plans .seats-url[data-gauge-id='+orphan.gauge_id+']').length > 0 ? $('#plans .seats-url[data-gauge-id='+orphan.gauge_id+']').closest('.seated-plan') : $('.gauge[data-gauge-id='+orphan.gauge_id+'] .seated-plan.picture'))
      .find('.seat[data-id='+orphan.seat_id+'].txt')
      .addClass('in-progress');
    
    // blinking
    var delay = 500;
    var blink = function(){
      if ( !oelt.hasClass('blink') )
        return;
      oelt.toggleClass('printed');
      setTimeout(blink, delay);
    }
    setTimeout(blink,delay);
    setTimeout(function(){
      oelt
        .removeClass('printed')
        .removeClass('blink');
      if ( !oelt.hasClass('ordered') ) // keep the "in-progress" if the seat has been booked in the timelap
        oelt.removeClass('in-progress');
    }, delay*20);
    
    clearInterval(interval);
  },500);
}


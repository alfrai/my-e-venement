// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
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
  LI.pubAfterRenderingSeats['pubReadyOrphans'] = { one_shot: true, options: { gauge_id: hash[1] }, exec: function(){
    LI.pubCheckOrphansVisually($('#ajax-pre-submit').prop('href'), options.gauge_id);
  }}
  
  // on changing quantities
  $('.prices .seats .seat[data-seat-id=""], .prices .seats .seat:not([data-seat-id]').each(function(){
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
          
          // sliding down WIP seats or creating new tickets
          for ( i = 0 ; i < $(this).val() - val ; i++ )
          {
            // preparing the DB
            seats.push({
              seat_id: $(this).closest('.gauge').find('.prices .seating.in-progress .seat input').eq(i).val(),
              gauge_id: $(this).closest('[data-gauge-id]').attr('data-gauge-id'),
              price_id: $(this).closest('[data-price-id]').attr('data-price-id')
            });
          }
          
          // sliding up normal tickets into WIPs, or removing them if they do not have Seat
          for ( i = 0 ; i < $(this).closest('[data-price-id]').find('.seat').length - $(this).val() ; i++ )
          if ( $(this).closest('[data-price-id]').find('.seat').eq(i).length > 0 )
          {
            // preparing the DB
            seats.push({
              seat_id: $(this).closest('[data-price-id]').find('.seat').eq(i).attr('data-seat-id'),
              ticket_id: $(this).closest('[data-price-id]').find('.seat').eq(i).attr('data-ticket-id'),
              gauge_id: $(this).closest('[data-gauge-id]').attr('data-gauge-id'),
            });
          }
          
          // in the DB
          if ( seats.length > 0 )
          $.get($('#mod-seated-tickets').prop('href'), { seats: seats }, function(json){
            // in the list
            LI.alert(json.error ? json.error.message : json.success.message, json.error ? 'error' : 'success');
            
            if ( !json.success )
              return;
            LI.pubCompleteSeatsList(json.success);
          });
        }
      })
    ;
  });
});

LI.pubAfterRenderingSeats = {};
// a trick specific to "pub" to execute a function only once after rendering the seated plan
LI.seatedPlanInitializationFunctions.push(function(){
  $.each(LI.pubAfterRenderingSeats, function(key, infos){
    if ( typeof(infos.exec) != 'function' )
    {
      if ( typeof(infos) == 'function' )
        infos();
      return;
    }
    
    console.log('After rendering the seats, function '+key+' is being executed.');
    infos.exec(infos.options);
    if ( infos.one_shot == true )
      delete LI.pubAfterRenderingSeats[key];
  });
});
  
LI.pubCompleteSeatsList = function(data)
{
  if ( !data )
  {
    console.log('Bad data given in LI.pubCompleteSeatsList()');
    return false;
  }
  
  // old
  if ( data.deleted )
  $.each(data.deleted, function(key, ticket){
    console.log('old #'+ticket.ticket_id);
    var tr = $('.gauge[data-gauge-id='+ticket.gauge_id+'] .prices '+(ticket.price_id ? '[data-price-id='+ticket.price_id+']' : '.seating.in-progress'));
    
    // seat names
    tr.find('.seats .seat[data-ticket-id='+ticket.ticket_id+']').remove();
  });
  
  // new
  if ( data.new )
  $.each(data.new, function(key, ticket){
    console.log('new #'+ticket.ticket_id);
    LI.pubAddWIPLineIfNecessary($('.gauge[data-gauge-id='+ticket.gauge_id+'] .prices'));
    
    $('<span></span>').addClass('seat')
      .attr('data-ticket-id', ticket.ticket_id)
      .attr('data-seat-id', ticket.seat_id)
      .text(ticket.seat_name ? ticket.seat_name : '')
      .append($('<input type="hidden" />').prop('name', 'price['+ticket.gauge_id+']['+ticket.price_id+'][seat_id][]').val(ticket.seat_id))
      .prependTo($('.gauge[data-gauge-id='+ticket.gauge_id+'] .prices '+(ticket.price_id ? '[data-price-id='+ticket.price_id+']' : '.seating.in-progress')+' .seats'))
    ;
  });
  
  // changed
  if ( data.moved )
  $.each(data.moved, function(key, ticket){
    console.log('changed #'+ticket.ticket_id);
    var elt = $('.gauge[data-gauge-id='+ticket.gauge_id+'] .prices .seats .seat[data-ticket-id='+ticket.ticket_id+']')
    if ( ticket.price_id )
    {
      var hidden = $('<input type="hidden" />').prop('name',
        'price'+
        '['+ticket.gauge_id+']'+
        '['+ticket.price_id+']'+
        '[seat_id][]'
      ).val(ticket.seat_id);
      elt.append(hidden)
        .prependTo($('.gauge[data-gauge-id='+ticket.gauge_id+'] .prices [data-price-id='+ticket.price_id+'] .seats'))
      ;
    }
    else
    {
      LI.pubAddWIPLineIfNecessary();
      elt.prependTo($(elt).closest('.gauge').find('.prices .seating.in-progress .seats'))
        .find('input').remove();
    }
  });
  
  // the quantities
  $('.gauge .prices tbody .quantity').each(function(){
    var tr = $(this).closest('tr');
    var nb = $(this).closest('tr').find('.seat').length;
    if ( $(this).closest('tr').is('[data-price-id]') )
      $(this).find('select').val(nb);
    else
      $(this).text(nb);
  });
  
  // orphans
  if ( data.orphans )
  $.each(data.orphans, function(gid, gauge){
  $.each(gauge, function(key, orphan){
    LI.pubShowOrphansOnPlan(orphan);
  }); });
}
  
LI.pubAfterRenderingSeats['pubSeatedPlanInitMain'] = function(){
  $('.seated-plan .seat.txt').unbind('contextmenu').click(function(){
    var seat = this;
    if ( $(seat).is('.ordered.in-progress') )   // removing a seat
    {
      $.get($(seat).closest('.full-seating').find('.remove-ticket').prop('href'), { seat_id: $(seat).attr('data-id') }, function(json){
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
        LI.pubCompleteSeatsList(json.success);
      });
    }
    else  // adding a seat
    {
      $.get($(seat).closest('.full-seating').find('.add-seat').prop('href'), {
        seat_id: $(seat).attr('data-id')
      }, function(json){
        console.log('after ajax adding');
        if ( json.error && json.error.message )
          LI.alert(json.error.message, 'error');
        
        if ( !json.success )
          return;
        
        // on the plan
        $(seat).parent().find('.seat-'+$(seat).attr('data-id'))
          .addClass('in-progress')
          .addClass('ordered');
        
        // message
        if ( json.success.message )
          LI.alert(json.success.message, 'success');
        
        // in the list
        LI.pubCompleteSeatsList(json.success);
      });
    }
  });
}

LI.pubAddWIPLineIfNecessary = function(elt){
  if ( elt == undefined )
    elt = $('.seated-plan.picture');
  
  $(elt).each(function(){
    if ( $(this).closest('.gauge').find('.prices tbody .seating.in-progress').length > 0 )
      return;
    
    var tr = $(this).closest('.gauge').find('.prices tbody tr[data-price-id]:first').clone(true);
    tr.removeAttr('data-price-id')
      .addClass('seating').addClass('in-progress')
      .prependTo($(this).closest('.gauge').find('.prices tbody'));
    tr.find('.seats').text('');
    tr.find('.price, .value, .quantity, .total').text('-');
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
      
      // reload the plan if some orphans are detected
      $.each(json.success.orphans, function(gid, gauge){
        LI.seatedPlanLoadData(
          $('.gauge[data-gauge-id='+gid+'] .full-seating .load-data').prop('href'),
          '#'+$('.gauge[data-gauge-id='+gid+'] .seated-plan').prop('id')
        );
        $('.gauge[data-gauge-id='+gid+']').click();
      });
    }
  });
}

LI.pubShowOrphansOnPlan = function(orphan)
{
  // visual
  $('.gauge[data-gauge-id='+orphan.gauge_id+']').click();
  var oelt =
  $('.gauge[data-gauge-id='+orphan.gauge_id+'] .seated-plan.picture .seat[data-id='+orphan.seat_id+']')
    .addClass('printed').addClass('blink');
  $('.gauge[data-gauge-id='+orphan.gauge_id+'] .seated-plan.picture .seat[data-id='+orphan.seat_id+'].txt')
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
}


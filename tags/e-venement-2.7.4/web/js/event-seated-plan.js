// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};


  // transforms a simple HTML call into a seated plan widget (seated-plan.css is also needed)
  // you can use something as simple as <a href="<?php url_for('seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id') ?>" class="picture seated-plan"><?php echo $seated_plan->Picture->getHtmlTag(array('title' => $seated_plan->Picture)) ?></a>
  LI.seatedPlanInitialization = function(root)
  {
    if ( root == undefined )
      root = $('body');
    
    $(root).find('a.picture.seated-plan img').each(function(){
      var widget = $(this).parent();
      var url = widget.prop('href');
      var elt = $('<span></span>').prop('class',widget.prop('class')).attr('style',widget.attr('style'))
        .append($('<div></div>').addClass('anti-handling'))
        .prepend($(this));
      widget.replaceWith(elt);
      
      // loads the content/data
      $.get(url,function(json){
        for ( i = 0 ; i < json.length ; i++ )
        {
          data = json[i];
          data.object = elt;
          LI.seatedPlanMouseup(data);
        }
        
        // triggers
        while ( fct = LI.seatedPlanInitializationFunctions.shift() )
          fct();
      });
      
      $(this).unbind('load').load(function(){
        // to avoid graphical bugs, relaunch the box resizing
        if ( $(this).height() == 0 )
        {
          // display and remove a clone of the current image simply to get its sizes
          clone = $(this).clone().appendTo('#footer');
          $(this).height(clone.height()).width(clone.width());
          clone.remove();
        }
        
        // box resizing
        $(this).parent()
          .css('display', 'block')
          .width($(this).width())
          .height($(this).height());
      });
    });
  }

  // the function that add a seat on every click (mouseup) or on data loading
  LI.seatedPlanMouseup = function(data)
  {
    // removing pre-seat and pre-seat behaviour
    $('.picture.seated-plan .pre-seat').remove();
    
    var position = data.position;
    var ref = $(data.object);
    var name = data.name;
    var diameter = data.diameter == undefined ? $(ref).closest('form').find('[name="seated_plan[seat_diameter]"]').val() : data.diameter;
    var occupied = data.occupied == undefined ? false : data.occupied;
    
    // the seat's name
    if ( name == undefined && $('.sf_admin_form_field_show_picture').length > 0 )
    {
      if ( $('.sf_admin_form_field_show_picture .donotask').is(':checked')
        && $('.sf_admin_form_field_show_picture .seat:first').length > 0 )
      {
        name =
          $('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(/\d+/,'')+
          (parseInt($('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(new RegExp($('.sf_admin_form_field_show_picture input.regexp').val()),''))+parseInt($('.sf_admin_form_field_show_picture .hop').val()));
      }
      else
      {
        name = prompt(
          $('.js_seated_plan_useful .prompt_seat_name').html(),
          $('.sf_admin_form_field_show_picture .seat:first').length == 0
          ? ''
          : $('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(/\d+/,'')+
            (parseInt($('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(new RegExp($('.sf_admin_form_field_show_picture input.regexp').val()),''))+parseInt($('.sf_admin_form_field_show_picture .hop').val()))
        );
      }
    }
    
    // avoid white space ending or beginning
    if ( name != undefined )
      $.trim(name);
    
    // need a non empty string
    if ( !name )
      return false;
      
    // then verify its unicity for this seated plan
    if ( data.record && $('.sf_admin_form_field_show_picture').length > 0 )
    if ( $('.sf_admin_form_field_show_picture .seat .txt[value="'+name+'"]').length > 0 )
    {
      alert($('.js_seated_plan_useful .alert_seat_duplicate').html());
      return false;
    }
    
    // adding the seat / plot
    $('<div class="seat"><input class="txt" type="hidden" value="'+name+'" /></div>')
      .attr('title', occupied && occupied['transaction_id'] ? name+' ('+occupied.transaction_id+(occupied.spectator ? ', '+occupied.spectator : '')+')' : name)
      .addClass('seat-'+$('.picture.seated-plan .seat').length).addClass(occupied ? occupied.type : '')
      .width(diameter).height(diameter)
      .each(function(){
        // width/2 to find the center
        $(this)
          .css('left', (x = Math.round(position['x']-($(this).width())/2))+'px')
          .css('top',  (y = Math.round(position['y']-($(this).width())/2))+'px');
      }).prependTo(ref)
      .clone(true).addClass('txt').prependTo(ref)
      
      // plot removal
      .dblclick(function(event){
        // reset a seat allocation ...
        if ( $('.sf_admin_form_field_show_picture').length == 0
          && $(this).is('.in-progress')
          && !$(this).is('.printed')
          && $('.reset-a-seat').length > 0 )
        {
          LI.seatedPlanUnallocatedSeat(this);
          return; // ... and this only
        }
        
        // DB removal
        var seat = this;
        $('.js_seated_plan_useful .seat_del').each(function(){
          $(this).find('[name="seat[name]"]').val(name);
          $.ajax({
            url: $(this).prop('action'),
            data: $(this).serialize(),
            complete: function(){
              // graphical removal
              $(seat).parent().find('.seat.'+$(seat).clone(true).removeClass('seat').removeClass('txt').attr('class')).remove();
              $('.sf_admin_form_field_show_picture .pre-seat').remove();
            }
          });
        });
      });
    
    // DB seat recording
    if ( data.record && $('.sf_admin_form_field_show_picture').length > 0 )
    $('.js_seated_plan_useful .seat_add').each(function(){
      $(this).find('[name="seat[name]"]').val(name);
      $(this).find('[name="seat[x]"]').val(position['x']);
      $(this).find('[name="seat[y]"]').val(position['y']);
      $(this).find('[name="seat[diameter]"]').val($('#seated_plan_seat_diameter').val());
      $.ajax({
        url: $(this).prop('action'),
        data: $(this).serialize(),
        error: function(){
          alert($('.js_seated_plan_useful .save_error').html());
          $('.sf_admin_form_field_show_picture .seat.txt:first').dblclick();
        }
      });
    });
  }

  LI.seatedPlanUnallocatedSeat = function(seat)
  {
    if ( $('#todo').length == 0 && !confirm($('form.reset-a-seat:first .confirm').html()) )
      return false;
    
    $('form.reset-a-seat:first [name="ticket[numerotation]"]').val($(seat).find('input').val());
    var id = $(seat).clone(true).removeClass('seat').removeClass('txt').removeClass('ordered').removeClass('in-progress').attr('class');
    $('form.reset-a-seat:first').unbind().submit(function(){
      $.ajax({
        url: $('form.reset-a-seat:first').prop('action'),
        data: $('form.reset-a-seat:first').serialize(),
        success: function(){
          $('.seated-plan .'+id).removeClass('ordered').removeClass('in-progress');
        },
      });
      return false;
    }).submit();
  }
  
  $(document).ready(function(){
    LI.seatedPlanInitializationFunctions = [];
    // automagically loads plans when the HTML call is in the page
    LI.seatedPlanInitialization();
    
    // background
    $('#seated_plan_background').change(function(){
      $('.sf_admin_form_field_show_picture .picture')
        .css('background-color', $(this).val());
    }).change();
    
    // seat pre-plots
    $('.sf_admin_form_field_show_picture .picture').mousedown(function(event){
      // left click
      if ( event.which != 1 )
        return;
      
      ref = $(this);
      
      if ( scale == undefined )
        scale = 1;
      position = {
        x: Math.round((event.pageX-ref.position().left)/scale),
        y: Math.round((event.pageY-ref.position().top) /scale)
      };
      
      // the graphical pre-seat
      $('<div class="pre-seat"></div>')
        .addClass('pre-seat-'+$('.sf_admin_form_field_show_picture .seat').length)
        .css('width', $('#seated_plan_seat_diameter').val()+'px')
        .css('height', $('#seated_plan_seat_diameter').val()+'px')
        .each(function(){
          $(this)
            .css('left', (x = Math.round(position['x']-$(this).width()/2))+'px')
            .css('top',  (y = Math.round(position['y']-$(this).width()/2))+'px');
        }).prependTo(ref);
      
      // adding a behaviour to pre-seat
      $('.sf_admin_form_field_show_picture .picture .anti-handling').mousemove(function(event){
        ref = $(this).parent();
        
        if ( scale == undefined )
          scale = 1;
        position = {
          x: Math.round((event.pageX-ref.position().left)/scale),
          y: Math.round((event.pageY-ref.position().top) /scale)
        };
        
        // moving
        ref.find('.pre-seat').each(function(){
          $(this)
            .css('left', (x = Math.round(position['x']-$(this).width()/2))+'px')
            .css('top',  (y = Math.round(position['y']-$(this).width()/2))+'px');
        });
      });
    });
    
    // seat plots
    $('.sf_admin_form_field_show_picture .picture .anti-handling').mouseup(function(event){
      // left click
      if ( event.which != 1 )
        return;
      
      if ( scale == undefined )
        scale = 1;
      
      return LI.seatedPlanMouseup({
        position: {
          x: Math.round((event.pageX-ref.position().left)/scale),
          y: Math.round((event.pageY-ref.position().top) /scale)
        },
        object: $(this).parent(),
        record: true,
      });
    });
    
    // removing last plot
    $(document).keypress(function(event){
      if ( event.which == 122 && event.ctrlKey )
        $('.sf_admin_form_field_show_picture .seat.txt:first').dblclick();
    });
    
    // magnifying
    var scale = 1;
    $('.sf_admin_form_field_show_picture .tools .magnify-in').click(function(){
      $('.sf_admin_form_field_show_picture .picture').css('transform','scale('+(scale = scale*1.1)+')');
      return false;
    });
    $('.sf_admin_form_field_show_picture .tools .magnify-out').click(function(){
      $('.sf_admin_form_field_show_picture .picture').css('transform','scale('+(scale = scale/1.1)+')');
      return false;
    });
    $('.sf_admin_form_field_show_picture .tools .magnify-zero').click(function(){
      $('.sf_admin_form_field_show_picture .picture').css('transform','scale('+(scale = 1)+')');
      return false;
    });
    if ( $('.sf_admin_form_field_show_picture .tools .magnify-in').length > 0 )
    {
      $(document).keyup(function(event){
        if ( event.key == 'Subtract' || event.which == 54 )
          $('.sf_admin_form_field_show_picture .tools .magnify-out').click();
        if ( event.key == 'Add' || event.which == 61 )
          $('.sf_admin_form_field_show_picture .tools .magnify-in').click();
        if ( (event.which == 96 || event.which == 48 && event.shiftKey) && event.ctrlKey )
          $('.sf_admin_form_field_show_picture .tools .magnify-zero').click();
      });
    }
  });

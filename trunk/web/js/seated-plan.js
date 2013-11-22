  $(document).ready(function(){
    // background
    $('#seated_plan_background').change(function(){
      $('.sf_admin_form_field_show_picture .picture')
        .css('background-color', $(this).val());
    }).change();
    
    // seat plots precondition for a good display
    $('.picture.seated-plan img').load(function(){
      // to avoid graphical bugs, relaunch the box resizing
      if ( $(this).height() == 0 )
      {
        var img = this;
        setTimeout(function(){ $(img).load(); },1500);
        return;
      }
      
      // box resizing
      $(this).parent()
        .css('display', 'block')
        .width($(this).width())
        .height($(this).height());
    });
    
    // seat pre-plots
    $('.sf_admin_form_field_show_picture .picture').mousedown(function(event){
      // left click
      if ( event.which != 1 )
        return;
      
      ref = $(this);
      position = {
        x: Math.round(event.pageX-ref.position().left),
        y: Math.round(event.pageY-ref.position().top)
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
        position = {
          x: Math.round(event.pageX-ref.position().left),
          y: Math.round(event.pageY-ref.position().top)
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
      
      return seated_plan_mouseup({
        position: {
          x: Math.round(event.pageX-ref.position().left),
          y: Math.round(event.pageY-ref.position().top)
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
    
  });
  
  // the function that add a seat on every click (mouseup) or on data loading
  function seated_plan_mouseup(data)
  {
    // removing pre-seat and pre-seat behaviour
    $('.picture.seated-plan .pre-seat').remove();
    
    var position = data.position;
    var ref = $(data.object);
    var name = data.name;
    var diameter = data.diameter;
    
    // the seat's name
    if ( name == undefined && $('.sf_admin_form_field_show_picture').length > 0 )
    {
      if ( $('.sf_admin_form_field_show_picture .donotask').is(':checked')
        && $('.sf_admin_form_field_show_picture .seat:first').length > 0 )
      {
        name =
          $('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(/\d+/,'')+
          (parseInt($('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(new RegExp($('.sf_admin_form_field_show_picture .regexp').val()),''))+parseInt($('.sf_admin_form_field_show_picture .hop').val()));
      }
      else
      {
        name = prompt(
          $('.js_seated_plan_useful .prompt_seat_name').html(),
          $('.sf_admin_form_field_show_picture .seat:first').length == 0
          ? ''
          : $('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(/\d+/,'')+
            (parseInt($('.sf_admin_form_field_show_picture .seat:first').attr('title').replace(new RegExp($('.sf_admin_form_field_show_picture .regexp').val()),''))+parseInt($('.sf_admin_form_field_show_picture .hop').val()))
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
    $('<div class="seat" title="'+name+'"><input class="txt" type="hidden" value="'+name+'" /></div>')
      .addClass('seat-'+$('.picture.seated-plan .seat').length)
      .width(diameter)
      .height(diameter)
      .each(function(){
        // width/2 to find the center
        $(this)
          .css('left', (x = Math.round(position['x']-($(this).width())/2))+'px')
          .css('top',  (y = Math.round(position['y']-($(this).width())/2))+'px');
      }).prependTo(ref)
      .clone(true).addClass('txt').prependTo(ref)
      
      // plot removal
      .dblclick(function(event){
        if ( $('.sf_admin_form_field_show_picture').length == 0 )
          return;
        
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

$(document).ready(function(){
  // the Hold's name in the HoldTransactions list
  $('.mod-hold_transaction .sf_admin_list caption h1').text(
    $('.mod-hold_transaction .sf_admin_list caption h1').text().replace('##hold##','"'+$('#sf_admin_footer #hold_name').text()+'"')
  );
  
  // the "next" 3 holds
  LI.holdSetColor($('body'));
  LI.holdGetNext($('#sf_admin_footer #next'), 0)
  
  // adding tickets in the hold_transaction/index
  $('.sf_admin_list .sf_admin_action_plus a, .sf_admin_list .sf_admin_action_minus a').unbind('click').click(function(){
    var row = $(this).closest('.sf_admin_row');
    $.ajax({
      url: $(this).prop('href'),
      type: 'get',
      success: function(json){
        $('#transition .close').click();
        if ( !json.id || json.quantity === undefined || json.quantity === false )
        {
          LI.alert('An error occurred (02)', 'error');
          return;
        }
        
        var row = $('.sf_admin_list .sf_admin_row [name="ids[]"][value="'+json.id+'"]').closest('.sf_admin_row');0
        row.find('.sf_admin_list_td_pretickets').text(json.quantity);
        row.find('.sf_admin_list_td_list_nb').text(parseInt(row.find('.sf_admin_list_td_list_nb_seated').text(),10) > json.quantity
          ? parseInt(row.find('.sf_admin_list_td_list_nb_seated').text(),10)
          : json.quantity
        );
        
        // highlighting
        $('#sf_admin_footer [name="nb_seats"]').change();
      },
      error: function(){
        $('#transition .close').click();
        LI.alert('An error occurred (01)', 'error');
      }
    });
    return false;
  });
  
  // highlighting the limit
  $('#sf_admin_footer [name="nb_seats"]').change(function(){
    // calculating the total
    $('.sf_admin_list .sf_admin_row.li-total').remove();
    $('.sf_admin_list .sf_admin_row:last').each(function(){
      if ( $(this).find('td').length <= 1 )
        return;
      
      $(this).clone()
        .insertAfter($(this))
        .addClass('li-total')
        .removeClass('li-direct-out-of-hold').removeClass('li-out-of-hold').toggleClass('odd').removeClass('ui-state-hover')
        .mouseenter(function(){ $(this).addClass('ui-state-hover'); }).mouseleave(function(){ $(this).removeClass('ui-state-hover'); })
        .find('td').text('').siblings('.sf_admin_number').each(function(){
          var cpt = 0;
          $('.sf_admin_list .sf_admin_row:not(.li-total) .'+$(this).attr('class').replace(/\s+/, ' ').replace(' ', '.')).each(function(){
            cpt += parseInt($(this).text(),10);
          });
          $(this).text(cpt);
        })
        .siblings(':last').text($('#sf_admin_footer [name="nb_seats"]').val()).addClass('sf_admin_number');
      ;
    });
  }).change();
  
  // drag'n'drop on the list's elements
  $('.sf_admin_list tbody').sortable({
    cursor: 'move',
    delay: 150,
    update: function(event, ui){
      // dealing w/ the total line
      if ( ui.item.hasClass('li-total')
        || ui.item.prev().hasClass('li-total')
        || ui.item.next().hasClass('li-total') )
      {
        $('#sf_admin_footer [name="nb_seats"]').change();
        return;
      }
      
      if ( (ui.item.prev().length > 0 && parseInt(ui.item.prev().find('.sf_admin_list_td_rank').text(),10) >= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
        || (ui.item.next().length > 0 && parseInt(ui.item.next().find('.sf_admin_list_td_rank').text(),10) <= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
      )
      {
        var url = $('#change-rank').prop('href')
          .replace($('#change-rank').attr('data-replace-bigger'),      ui.item.prev().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-smaller'),     ui.item.next().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-this'),        ui.item.find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-hold-before'), parseInt(ui.item.prev().find('.sf_admin_list_td_hold_id').text(),10))
          .replace($('#change-rank').attr('data-replace-hold-after'),  parseInt(ui.item.next().find('.sf_admin_list_td_hold_id').text(),10))
        ;
        $.ajax({
          url: url,
          type: 'get',
          success: function(json){
            if ( json.reload )
              location.reload();
            
            if ( !json.rank )
              LI.alert('An error occurred (02)', 'error');
            $('.sf_admin_list [name="ids[]"][value='+json.id+']').closest('.sf_admin_row').find('.sf_admin_list_td_rank').text(json.rank);
            $('#sf_admin_footer [name="nb_seats"]').change();
          },
          error: function(){
            LI.alert('An error occurred (01)', 'error');
            location.reload();
          }
        });
      }
    }
  }).find('tr').unbind('click');
  
  // auto-validate contacts for transactions
  $('.sf_admin_list_td_list_contact form input[type=hidden], .sf_admin_list_td_list_professional form select').change(function(){
    if ( location.hash == '#debug' )
      console.error('change');
    $(this).closest('form').submit();
  });
  $('.sf_admin_list_td_list_contact form, .sf_admin_list_td_list_professional form').submit(function(){
    $.ajax({
      url: $(this).prop('action'),
      method: $(this).prop('method'),
      data: $(this).serialize(),
      error: function(){ LI.alert('An error occurred (01)', 'error'); },
      success: function(json){
        if ( location.hash == '#debug' )
          console.error(json);
        
        if ( json.id === undefined || json.contact_id === undefined && json.professional_id === undefined )
        {
          LI.alert('An error occurred (02)', 'error');
          return;
        }
        
        if ( json.professionals !== undefined )
        {
          var select = $('.sf_admin_list [name="ids[]"][value="'+json.id+'"]').closest('.sf_admin_row')
            .find('.sf_admin_list_td_list_professional select');
          select.find('option').remove();
          select.append('<option></option>');
          $.each(json.professionals, function(i, pro){
            $('<option></option>').val(pro.id).text(pro.name)
              .appendTo(select);
          });
        }
        
        if ( location.hash == '#debug' )
          LI.alert('Success', 'success');
      }
    });
    return false;
  });
});

if ( LI == undefined )
  var LI = {};
LI.holdGetNext = function(elt, i)
{
  var url = elt.prop('href').replace(elt.attr('data-replace-hold'),elt.find('input').val());
  $.ajax({
    url: url,
    method: 'get',
    success: function(data){
      i++;
      data = $.parseHTML(data);
      var tr = $('.sf_admin_list > table > tbody > tr:not(.li-total):last');
      LI.holdSetColor(data);
      
      // some graphical tweakings
      $(data).find('.sf_admin_list > table > tbody > tr').removeClass('odd');
      
      // adding the new rows
      if ( $(data).find('.sf_admin_list > table > tbody > tr > td').length > 1 )
        $(data).find('.sf_admin_list > table > tbody > tr').insertAfter(tr);
      
      // calculating totals
      $('#sf_admin_footer [name="nb_seats"]').val(
        parseInt($('#sf_admin_footer [name="nb_seats"]').val(),10)
        +
        parseInt($(data).find('#sf_admin_footer [name="nb_seats"]').val(),10)
      ).change();
      
      // next feeder
      if ( i < 3 && $(data).find('#sf_admin_footer #next input').val() )
        LI.holdGetNext($(data).find('#sf_admin_footer #next'), i);
    }
  });
}

LI.holdSetColor = function(root)
{
  var color = $(root).find('#sf_admin_footer [name=hold_color]').val();
  var opacity = 0.1;
  $(root).find('.sf_admin_list > table > tbody > tr').attr('data-color', color); 
  if ( color.indexOf('#') != -1 )
  {
    color = LI.hexToRgb(color);
    $(root).find('.sf_admin_list > table > tbody > tr').each(function(){
      var final_color = 'rgba('+color.r+','+color.g+','+color.b+','+(opacity)+')';
      
      var state = $(this).find('.sf_admin_list_td_list_state > *');
      if ( state.hasClass('li-direct-out-of-hold') )
        final_color = 'rgba('+color.r+','+color.g+','+color.b+','+(opacity*1.8)+')';
      if ( state.hasClass('li-out-of-hold') )
        final_color = 'rgba('+color.r+','+color.g+','+color.b+','+(opacity*2.5)+')';
      
      $(this).find('td').css('background-color', final_color);
    });
  }
  else
    $(root).find('.sf_admin_list > table > tbody > tr > td').css('background-color', color);
}

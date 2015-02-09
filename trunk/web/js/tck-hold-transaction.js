$(document).ready(function(){
  // the Hold's name in the HoldTransactions list
  var holds = [];
  $('.sf_admin_list .sf_admin_list_td_Hold').each(function(){
    if ( holds.indexOf($(this).text()) == -1 )
      holds.push($(this).text());
  });
  if ( holds.length == 1 )
    $('.mod-hold_transaction .sf_admin_list caption h1').append(' "'+$.trim(holds[0])+'"');
  
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
          console.error(json);
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
    $('.sf_admin_list .sf_admin_row').removeClass('li-out-of-hold').removeClass('li-direct-out-of-hold');
    var qty = parseInt($(this).val(),10);
    
    // first check for already seated tickets
    $('.sf_admin_list .sf_admin_row').each(function(){
      qty -= parseInt($(this).find('.sf_admin_list_td_list_nb_seated').text(),10);
    });
    
    // second check for the rest of the tickets
    $('.sf_admin_list .sf_admin_row').each(function(){
      var pretickets =
        parseInt($(this).find('.sf_admin_list_td_pretickets').text(),10) -
        parseInt($(this).find('.sf_admin_list_td_list_nb_seated').text(),10)
      ;
      if ( pretickets < 0 )
        pretickets = 0;
      
      // case of qty already < 0, if no ticket already seated is included
      if ( qty <= 0 && parseInt($(this).find('.sf_admin_list_td_list_nb_seated').text(),10) == 0 )
      {
        $(this).addClass('li-direct-out-of-hold');
        return;
      }
      
      qty -= pretickets;
      if ( qty < 0 && pretickets > 0 )
        $(this).addClass('li-out-of-hold');
      else if ( pretickets == 0 )
        $(this).addClass('li-seated');
    });
    
    // calculating the total
    $('.sf_admin_list .sf_admin_row.li-total').remove();
    $('.sf_admin_list .sf_admin_row:last').each(function(){
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
        $('#sf_admin_footer [name="nb_seats"]').change();
      
      if ( (ui.item.prev().length > 0 && parseInt(ui.item.prev().find('.sf_admin_list_td_rank').text(),10) >= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
        || (ui.item.next().length > 0 && parseInt(ui.item.next().find('.sf_admin_list_td_rank').text(),10) <= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
      )
      {
        var url = $('#change-rank').prop('href')
          .replace($('#change-rank').attr('data-replace-bigger'), ui.item.prev().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-smaller'),  ui.item.next().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-this'),  ui.item.find('[name="ids[]"]').val())
        ;
        $.ajax({
          url: url,
          type: 'get',
          success: function(json){
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
  });
});

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
        if ( !json.id || !json.quantity )
        {
          LI.alert('An error occurred (02)', 'error');
          return;
        }
        
        $('.sf_admin_list .sf_admin_row [name="ids[]"][value="'+json.id+'"]')
          .closest('.sf_admin_row').find('.sf_admin_list_td_list_nb')
          .text(json.quantity);
      },
      error: function(){
        $('#transition .close').click();
        LI.alert('An error occurred (01)', 'error');
      }
    });
    return false;
  });
  
  // drag'n'drop on the list's elements
  $('.sf_admin_list tbody').sortable({
    cursor: 'move',
    delay: 150,
    update: function(event, ui){
      console.error('move');
      if ( (ui.item.prev().length > 0 && parseInt(ui.item.prev().find('.sf_admin_list_td_rank').text(),10) >= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
        || (ui.item.next().length > 0 && parseInt(ui.item.next().find('.sf_admin_list_td_rank').text(),10) <= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
      )
      {
        console.error('movable');
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

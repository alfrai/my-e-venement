$(document).ready(function(){
  // the Hold's name
  var holds = [];
  $('.sf_admin_list .sf_admin_list_td_Hold').each(function(){
    if ( holds.indexOf($(this).text()) == -1 )
      holds.push($(this).text());
  });
  if ( holds.length == 1 )
    $('.mod-hold_transaction .sf_admin_list caption h1').append(' "'+$.trim(holds[0])+'"');
  
  // drag'n'drop
  $('.sf_admin_list tbody').sortable({
    cursor: 'move',
    delay: 150,
    update: function(event, ui){
      if ( (ui.item.prev().length > 0 && parseInt(ui.item.prev().find('.sf_admin_list_td_rank').text(),10) < parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
        || (ui.item.next().length > 0 && parseInt(ui.item.next().find('.sf_admin_list_td_rank').text(),10) > parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
      )
      {
        var url = $('#change-rank').prop('href')
          .replace($('#change-rank').attr('data-replace-smaller'), ui.item.prev().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-bigger'),  ui.item.next().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-this'),  ui.item.find('[name="ids[]"]').val())
        ;
        $.ajax({
          url: url,
          type: 'get',
          success: function(json){
            $('.sf_admin_list [name="ids[]"][value='+json.id+']').closest('.sf_admin_row').find('.sf_admin_list_td_rank').text(json.rank);
          },
          error: function(){
            LI.alert('An error occurred', 'error');
            location.reload();
          }
        });
      }
    }
  });
});

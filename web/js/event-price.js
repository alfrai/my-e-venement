$(document).ready(function(){

  // drag'n'drop on the list's elements
  $('.sf_admin_list tbody').sortable({
    cursor: 'move',
    delay: 150,
    update: function(event, ui){
      if ( (ui.item.prev().length > 0 && parseInt(ui.item.prev().find('.sf_admin_list_td_rank').text(),10) >= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
        || (ui.item.next().length > 0 && parseInt(ui.item.next().find('.sf_admin_list_td_rank').text(),10) <= parseInt(ui.item.find('.sf_admin_list_td_rank').text(),10))
      )
      {
        var url = $('#change-rank').prop('href')
          .replace($('#change-rank').attr('data-replace-bigger'),      ui.item.prev().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-smaller'),     ui.item.next().find('[name="ids[]"]').val())
          .replace($('#change-rank').attr('data-replace-this'),        ui.item.find('[name="ids[]"]').val())
        ;
        
        if ( location.hash == '#debug' )
          window.open(url);
        else
        $.ajax({
          url: url,
          type: 'get',
          success: function(json){
            LI.alert(json.message, 'success');
            if ( json.reload )
              location.reload();
            
            if ( !json.rank )
              LI.alert('An error occurred (json)', 'error');
            $('.sf_admin_list [name="ids[]"][value='+json.id+']').closest('.sf_admin_row').find('.sf_admin_list_td_rank').text(json.rank);
            $('#sf_admin_footer [name="nb_seats"]').change();
          },
          error: function(){
            LI.alert('An error occurred (function)', 'error');
            location.reload();
          }
        });
      }
    }
  }).find('tr').unbind('click');
});

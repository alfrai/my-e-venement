$(document).ready(function(){
  // if the first page is displaid, then reload data every 3 seconds
  if ( $('.sf_admin_list .sf_admin_pagination tbody input[type=text]').val() == 1 )
    setInterval(function(){
      $.ajax({
        type: 'get',
        url: $('#sf_admin_footer a.root').prop('href'),
        success: function(data){
          data = $.parseHTML(data);
          $(data).find('#sf_admin_content .sf_admin_list > table > tbody .sf_admin_row [data-id]').each(function(){
            if ( $('#sf_admin_content .sf_admin_list > table > tbody .sf_admin_row [data-id='+$(this).attr('data-id')+']').length == 0 )
            {
              console.log('Adding a new line for the control '+$(this).attr('data-id'));
              $('#sf_admin_content .sf_admin_list > table > tbody').prepend($(this).closest('.sf_admin_row'));
              $('#sf_admin_content #sf_admin_pager .right').css('visibility', 'hidden');
            }
          });
        }
      });
    },5000);
});

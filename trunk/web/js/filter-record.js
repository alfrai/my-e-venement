function filter_record_init()
{
  $('#tdp-top-bar #sf_admin_filter_save').submit(function(){
    if (!( name = prompt($(this).find('[name=s]').attr('alt')) ))
      return false;
    
    $(this).find('[name="filter[name]"]').val(name);
    $.ajax({
      url: $(this).prop('action'),
      data: $(this).serialize(),
      method: 'post',
      success: function(data){ elt = $($.parseHTML(data)).find('.sf_admin_flashes .error, .sf_admin_flashes .notice'); if ( $(elt).length > 0 ) alert($(elt).text()); },
    });
    
    return false;
  }).appendTo($('.sf_admin_filter').closest('.ui-dialog').find('.ui-dialog-buttonset'));
  
  $('.ui-dialog-buttonset #sf_admin_filter_save a, .ui-dialog-buttonset #sf_admin_filter_save button')
    .mouseenter(function(){ $(this).addClass('ui-state-hover'); })
    .mouseleave(function(){ $(this).removeClass('ui-state-hover'); });
  
  $('#sf_admin_filter_save .filters-list').click(function(){
    $.get($(this).prop('href'), function(data){
      data = $.parseHTML(data);
      $(data).find('th:first-child input, td:first-child input').remove();
      
      // filter's deletion
      var jscode = $(data).find('.sf_admin_list > table > tbody .sf_admin_action_delete a').attr('onclick');
      $(data).find('.sf_admin_list > table > tbody .sf_admin_action_delete a').removeAttr('onclick');
      $(data).find('.sf_admin_list > table > tbody .sf_admin_action_delete a').click(function(){
        jscode = jscode.replace('function onclick(event) {','').replace('f.submit(); };return false;','')+' }';
        eval(jscode);
        if ( f !== undefined )
          $.ajax({
            url: $(f).prop('action'),
            data: $(f).serialize(),
            method: $(f).prop('method'),
            success: function(data){
              data = $.parseHTML(data);
              var elt = $(data).find('.sf_admin_flashes .error, .sf_admin_flashes .notice');
              if ( $(data).find('.sf_admin_flashes .error').length == 0 )
              {
                $('.ui-dialog:last .ui-dialog-titlebar-close').click();
                $('#sf_admin_filter_save .filters-list').click();
              }
              if ( $(elt).length > 0 )
                alert($(elt).text());
            },
          });
        return false;
      });
      
      // filter loading
      $(data).find('.sf_admin_list > table > tbody .sf_admin_action_show a').click(function(){
        $.get($(this).prop('href'),function(){
          alert('Filtre chargé.');
          location.reload();
        });
        return false;
      });
      $(data).find('.sf_admin_list > table > tbody tr')
        .mouseenter(function(){ $(this).addClass('ui-state-hover'); })
        .mouseleave(function(){ $(this).removeClass('ui-state-hover'); });
      
      $('<div class="sf_admin_list"></div>').append($('<table></table>').append($(data).find('.sf_admin_list > table > tbody'))).dialog({
        appendTo: 'body',
        title: $(data).find('.sf_admin_list table caption h1').text(),
        width: '80%',
        modal: true,
        closeOnEscape: true
      });
    });
    return false;
  });
}

$(document).ready(function(){ filter_record_init(); });

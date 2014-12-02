$(document).ready(function(){
  // hover on rows
  $('.summary tbody tr').mouseenter(function(){
    $(this).addClass('ui-state-hover');
  });
  $('.summary tbody tr').mouseleave(function(){
    $(this).removeClass('ui-state-hover');
  });
  
  // specific paging
  action = /\/summary\/(.*)\/action/.exec(window.location)[1];
  $('#sf_admin_pager a').each(function(){
    href = $(this).prop('href').replace(/\/summary\?/,'/summary/'+action+'/action?');
    $(this).prop('href',href);
  });
  $('#sf_admin_pager input[onkeypress]').removeAttr('onkeypress')
    .keypress(function(event){
      if ( event.keyCode == 13 )
      {
        uri = /^(.*)\?.*$/.exec(window.location)[1];
        window.location = uri+'?page='+$(this).val();
        $(this).closest('form').submit(function(){ return false; });
        return false;
      }
    });
  
  // specific filtering
  action = /\/summary\/(.*)\/action/.exec(window.location)[1];
  $('#sf_admin_filter form').each(function(){
    $(this).prop('action',$(this).prop('action')+'?type='+action);
  });
  $('#sf_admin_filters_buttons a').each(function(){
    $(this).prop('href',$(this).prop('href')+'&type='+action);
  });
  
  // calculate the page's total if there is something to calculate...
  debts = $('.sf_admin_list_td_debt .debt');
  if ( debts.length > 0 )
  {
    for ( i = total = 0 ; i < debts.length ; i++ )
      total += parseFloat($(debts[i]).html().replace(',','.'));
    currency = debts.first().html().replace(/^.*(&nbsp;.*)$/,'$1');
    tr = debts.first().closest('tr').clone()
      .addClass('total')
      .appendTo('.sf_admin_list > table > tbody');
    tr.find('td').html('');
    tr.find('.sf_admin_list_td_debt').html('<span class="debt">'+total.toFixed(2)+currency+'</span>');
  }
});

$(document).ready(function(){
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

$(document).ready(function(){
  debts_add_total();
  
  if ( window.list_scroll_beginning == undefined )
      window.list_scroll_beginning = new Array;
  if ( window.list_scroll_end == undefined )
      window.list_scroll_end = new Array;

  window.list_scroll_beginning.push(debts_remove_total);
  window.list_scroll_end.push(debts_add_total);
});

function debts_remove_total()
{
  $('.sf_admin_list .total').remove();
}

function debts_add_total()
{
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
}

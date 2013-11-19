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
      total += parseFloat($(debts[i]).html().replace(',','.').replace('&nbsp;',''));
    currency = debts.first().html().replace(/^.*(&nbsp;.*)$/,'$1');
    tr = debts.first().closest('tr').clone(true)
      .addClass('total')
      .appendTo('.sf_admin_list > table > tbody');
    tr.find('td').html('');
    tr.find('.sf_admin_list_td_debt').html('<span class="debt">'+total.toFixed(2)+currency+'</span>')
  }
  paid = $('.sf_admin_list_td_debt .paid');
  if ( paid.length > 0 )
  {
    for ( i = total = 0 ; i < paid.length ; i++ )
      total += parseFloat($(paid[i]).html().replace(',','.').replace('&nbsp;',''));
    $('.sf_admin_list > table > tbody tr:last .sf_admin_list_td_debt').prepend('<span class="paid">'+total.toFixed(2)+currency+'</span> =');
  }
  prices = $('.sf_admin_list_td_debt .price');
  if ( prices.length > 0 )
  {
    for ( i = total = 0 ; i < prices.length ; i++ )
      total += parseFloat($(prices[i]).html().replace(',','.').replace('&nbsp;',''));
    $('.sf_admin_list > table > tbody tr:last .sf_admin_list_td_debt').prepend('<span class="price">'+total.toFixed(2)+currency+'</span> -');
  }
}

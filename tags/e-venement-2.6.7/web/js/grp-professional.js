$(document).ready(function(){
  // for hypothetical pagination...
  if ( window.list_scroll_end == undefined )
    window.list_scroll_end = new Array()
  window.list_scroll_end[window.list_scroll_end.length] = grp_professional_total;
  grp_professional_total();
});

function grp_professional_total()
{
  // adding a last empty line to totals
  $('.sf_admin_list > table > tbody tr.sf_admin_total').remove();
  $('.sf_admin_list > table > tbody').append($('.sf_admin_list > table > tbody tr:first').clone().addClass('sf_admin_total'));
  $('.sf_admin_list > table > tbody tr.sf_admin_total > td').html('');
  
  // adding the calculated values
  $('.sf_admin_list > table > tbody .sf_admin_total > .sf_admin_numeric').each(function(){
    var total = 0;
    $('.sf_admin_list > table > tbody .'+$(this).clone().removeClass('sf_admin_numeric').prop('class')).each(function(){
      nb = parseInt($(this).html(),10);
      if ( isNaN(nb) )
        nb = 0;
      total += nb;
    });
    $(this).html(total);
  });
}

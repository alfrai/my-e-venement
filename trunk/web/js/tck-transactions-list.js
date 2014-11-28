$(document).ready(function(){
  setTimeout(function(){
    $('.sf_admin_list_td_list_payments_list').click(function(){
      $(this).find('ul ul').slideToggle();
    });
  },1000);
  
  $('.sf_admin_action_showup .fg-button').click(function(){
    $(this).toggleClass('tdp-opened');
    var elts = $(this).closest('tr').find('.sf_admin_list_td_list_order, .sf_admin_list_td_list_invoice, .sf_admin_list_td_list_transaction_id, .sf_admin_list_td_updated_at, .sf_admin_list_td_list_first_user, .sf_admin_list_td_created_at, .sf_admin_list_td_User');
    if ( !$(this).hasClass('tdp-opened') )
    {
      $(this).closest('tr').find('.sf_admin_list_td_list_details').hide().find('tbody > :not(.template)').remove();
      elts.show();
      $('#transition .close').click();
    }
    else
    {
      $(this).closest('tr').find('.sf_admin_list_td_list_details').prop('colspan',7).show();
      elts.hide();
      var elt = this;
      $.ajax({
        url: $(this).prop('href'),
        type: 'GET',
        success: function(json){
          $('#transition .close').click();
          $.each(json, function(type, content){
            $.each(content, function(id, pdt){
              var telt = $(elt).closest('tr').find('.sf_admin_list_td_list_details .'+type+' tbody .template').clone().removeClass('template');
              telt.appendTo($(elt).closest('tr').find('.sf_admin_list_td_list_details .'+type+' tbody'));
              telt.find('> *:not(.special)').each(function(){
                $(this).text(pdt[$(this).attr('class')]);
              });
              telt.find('> .special').each(function(){
                var attr = $(this).attr('class').replace(' special', '');
                telt.find('.'+attr+'.special a').prop('href', pdt[attr+'_url']).text(pdt[attr]);
              });
              if ( !pdt.sold )
                telt.addClass('not-sold');
              if ( pdt.cancelled )
                telt.addClass('cancelled');
            });
          });
        }
      });
    }
    
    return false;
  });
});

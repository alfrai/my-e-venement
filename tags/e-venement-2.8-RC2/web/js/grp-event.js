function grp_extra_empty_fields_cleanup()
{
  // still theorical function which aims to remove extra empty TicketForm
}
$(document).ready(function(){
  
  $(document).mouseup(function(){
    $('.grp-entry tbody .copy').removeAttr('title');
    
    $('.grp-entry tbody .copy').removeClass('copy');
    $('.grp-entry tbody').removeClass('move');
    
    $('.grp-entry tbody').unbind('mousemove');
  });
  
  $('.grp-entry tbody td:not(.contact):not(.ticketting)').unbind().mouseup(function(e){
    // preventing gauges' multi-calculation
    $('body').append('<div id="no-calculate-gauge"></div>');
    
    src = $('.grp-entry .copy');
    if ( !$(this).hasClass('copy')
      && src.length == 1 )
    {
      target = $(this);
      
      // deleting everything
      target.find('a.delete').click(); 
      target.find('form:last input[type=checkbox]:checked').click();
          
      // adding tickets
      src.find('form').each(function() {
        if ( $(this).find('input[type=text]').length > 0 && $(this).find('input[type=text]').val() != '' )
        {
          target.find('form:first input[type=text]').val($(this).find('input[type=text]').val());
          target.find('form:first select').val($(this).find('select').val());
          target.find('form:first').submit();
        }
      });
      
      // global statute
      if ( src.find('form:last input[type=checkbox]:first').prop('checked') )
        target.find('form:last input[type=checkbox]:first').click();
      if ( src.find('form:last input[type=checkbox]:last').prop('checked') )
        target.find('form:last input[type=checkbox]:last').click();
      target.find('form:last').submit();
      
      // deleting source
      if ( !e.ctrlKey && !e.metaKey )
      {
        src.find('a.delete').click();
        src.find('form:last input[type=checkbox]:checked').click();
        src.find('form').submit();
      }
      
      grp_extra_empty_fields_cleanup();
    }
    
    $('.grp-entry tbody .copy').removeAttr('title');
    $('.grp-entry tbody .copy').removeClass('copy');
    $('.grp-entry tbody').removeClass('move');
    $('.grp-entry tbody').unbind('mousemove');
    
    // recalculate gauges
    setTimeout(function(){
      $('#no-calculate-gauge').remove();
      calculate_gauges();
    },2000);
  });
  
  $('.grp-entry tbody td:not(.ticketting):not(.contact)').mousedown(function(){
    $(this).addClass('copy');
    $('.grp-entry tbody').addClass('move');
    
    $(this).prop('title',$('#copy-paste').html());
    $('.grp-entry tbody').mousemove(grp_mouse_move);
  });
  
  // the titles for manifestations' actions
  $('.manifestation .fg-button-mini').each(function(){
    $(this).prop('title',$.trim($(this).html()));
  });
  
  // hidding the gauges selection if there is only one choice
  $('.EntryTickets select[name="entry_tickets[gauge_id]"] option:only-child').each(function(){
    $(this).parent().replaceWith('<input type="hidden" value="'+$(this).val()+'" name="'+$(this).parent().prop('name')+'" />');
  });
  
  // hooking thead & tfoot
  if ( $.fn.tableScroll !== undefined )
  {
    var h = $(window).height()-250;
    $('table.grp-entry').hide();
    if ( $('#content').height() > h )
      h = $('#content').height();
    $('table.grp-entry').show();
    
    // HACK: minimizing content to optimize tableScroll calculation - it permits a gain of time of 50% on big arrays
    $('table.grp-entry tbody tr:first td').css('height',$('table.grp-entry tbody').height()+"px");
    trs = $('table.grp-entry tbody tr:not(:first)').clone();
    $('table.grp-entry tbody tr:not(:first)').remove();
    
    $('table.grp-entry').tableScroll({
      height: h - $('table.grp-entry thead').height() - $('table.grp-entry tfoot').height(),
    });
    
    // HACK: re-establishing content
    $('table.grp-entry tbody tr:first td').css('height','auto');
    $('table.grp-entry tbody').append(trs);
  }
  
  // using the print button ... to print
  $('.sf_admin_action_print a')
    .prepend('<span class="ui-icon ui-icon-print"></span>')
    .click(function(){
      var height = $('.tablescroll_wrapper').css('height');
      $('.tablescroll_wrapper').css('height','auto');
      $('#transition .close').click();
      print();
      $('.tablescroll_wrapper').css('height',height);
      return false;
    });
});

function grp_mouse_move(event)
{
}

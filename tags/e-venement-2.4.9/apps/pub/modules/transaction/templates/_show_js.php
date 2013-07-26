<script type="text/javascript"><!--
  $(document).ready(function(){
    // concatenation of tickets which has the same price
    while ( $('#command tbody .tickets > :not(.done)').length > 0 )
    {
      ticket = $('#command tbody .tickets > :not(.done)').first();
      price_class = ticket.attr('class');
      gauge_id = ticket.closest('tr').attr('id');
      ticket.closest('tr').find('.qty').html($('#command tbody #'+gauge_id+' .tickets > .'+price_class).length);
      value = 0;
      $('#command tbody #'+gauge_id+' .tickets > .'+price_class).each(function(){
        value += parseFloat($(this).closest('tr').find('.value').html().replace(',','.'));
      });
      currency = $.trim(ticket.closest('tr').find('.value').html()).replace(',','.').replace(/^\d+\.{0,1}\d*(&nbsp;.*)$/,'$1');
      
      ticket.closest('tr').find('.total').html(value.toFixed(2)+currency);
      ticket.addClass('done');
      $('#command tbody #'+gauge_id+' .tickets > .'+price_class+':not(.done)').remove();
    }
    
    // removing empty lines
    $('#command tbody tr').each(function(){
      if ( $(this).find('.tickets .done').length == 0 )
        $(this).remove();
    });
  });
--></script>

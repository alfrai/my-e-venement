<form action="#" method="get">
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#ts-tickets').closest('form').unbind().submit(function(){ return false; });
    ts_command();
  });
  
  function ts_command(manifestation_id = false)
  {
    $.get('<?php echo url_for('ticket/ticket?id='.$transaction->id) ?>?'+(manifestation_id ? encodeURIComponent('ticket[manifestation_id]')+'='+encodeURIComponent(manifestation_id) : ''),function(data){
      data = $.parseHTML(data);
      currency = $(data).find('.manifestations_list li.total .total').html().replace(/\d+[,\.]\d+/,'');
      
      $(data).find('.manifestations_list > ul > li:not(.total) .workspace [type=hidden]').each(function(){
        if ( $('#ts-tickets .manif.rendered [name="'+$(this).prop('name')+'"]').length == 0 )
        {
          price = $(this).prop('name').replace(/^ticket\[prices\]\[\d+\]\[/g,'').replace('][]','');
          var li = $(this).closest('li').clone(true).addClass('rendered');
          li.find('input[type=hidden]:not([name="ticket[gauge_id]"])').remove();
          li.find('input[type=radio]').removeAttr('checked');
          li.find('.manif').prepend($('<span class="ticket_prices"><input type="text" value="'+$(data).find('[name="'+$(this).prop('name')+'"]').length+'" class="nb" autocomplete="off" maxlength="3" name="hidden_nb" />&nbsp;<span class="price_name">'+price+'</span></span>'));
          li.append($(data).find('[name="'+$(this).prop('name')+'"]'));
          
          ts_tickets_total(li, currency);
          
          li.prependTo('#ts-tickets');
        }
        
        // if completing an existing transaction
        if ( manifestation_id )
        {
          manif = $('#ts-tickets .manif.rendered [name="'+$(this).prop('name')+'"]').closest('.manif.rendered');

          // the displaid tickets' number
          manif.find('.nb')
            .val($(data).find('[name="'+$(this).prop('name')+'"]').length)
            .get(0).defaultValue = manif.find('.nb').val();
          
          manif.append($(this).clone(true)); // the hidden inputs representing real tickets
          ts_tickets_total(manif, currency); // the price
        }
      });
      
      // empty stuff
      $('#ts-tickets .nb').each(function(){
        if ( $(this).val() < 1 )
          $(this).closest('.manif.rendered').remove();
      });
      
      // submitting a new qty
      $('#ts-tickets .manif.rendered .nb').unbind().change(function(){
        var nb = $('#ts-prices [name="ticket[nb]"]');
        nb.get(0).defaultValue = nb.val();
        nb.val($(this).val() - $(this).get(0).defaultValue);
        $('#ts-prices .show[name="ticket[price_name]"][value='+$(this).closest('.ticket_prices').find('.price_name').html()+']').click();
        nb.val(nb.get(0).defaultValue);
      });
      
      ts_manifestations_select();
      ts_tickets();
      $('#ts-tickets .manif.rendered.selected').click();
    }); // $.get()
  } // function
  
  function ts_tickets_total(manif, currency)
  {
    manif.find('.ticket_prices .total').remove();
    manif.find('.ticket_prices').append('<span class="total">0</span>');
    manif.find('.workspace [type=hidden])').each(function(){
      $(this).closest('.manif.rendered').find('.ticket_prices .total').html(
        parseFloat($(this).closest('.manif.rendered').find('.ticket_prices .total').html())+parseFloat($(this).val()).toFixed(2)
      );
    });
    manif.find('.ticket_prices .total').html(manif.find('.ticket_prices .total').html()+currency);
  }
  
  function ts_tickets()
  {
    $('#ts-tickets li').click(function(){
      $('#ts-tickets .ts-tickets-list').remove();
      $('<li class="ts-tickets-list"></li>').insertAfter($(this));
      $(this).find('input[type=hidden]:not([name="ticket[gauge_id]"])').each(function(){
        $('#ts-tickets .ts-tickets-list').prop('title',$('#ts-tickets .ts-tickets-list').prop('title')+$(this).prop('alt')+"\n")
          .append('<span class="ticket '+$(this).prop('class')+'">'+$(this).prop('alt')+'</span> ');
      });
    });
  }
--></script>
<ul id="ts-tickets">
</ul>
</form>

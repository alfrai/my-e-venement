    <script type="text/javascript"><!--
      $(document).ready(function(){
        // NAVIGATION INSIDE PRICES
        $('#ts-prices .prices_list').prepend('<a href="#0" class="prices-nav"><span>..</span></a>').append('<a href="#14" class="prices-nav"><span>..</span></a>');
        $('#ts-prices .prices_list .prices-nav').click(function(){
          ts_prices(parseInt($(this).attr('href').substr(1),10));
          return false;
        });
        
        // ADDING SPECIFIC QUANTITY
        $('#ts-prices .prices_list input[type=text]').appendTo('#ts-prices .prices_list');
        
        // HIDING EXTRA PRICES
        $('#ts-prices .prices_list .prices-nav:first').click();
        
        <?php
          $ticket = new Ticket;
          $ticket->transaction_id = $transaction->id;
          $form = new TicketForm($ticket);
        ?>
        $('#ts-prices form').append('<?php echo $form->renderHiddenFields() ?>');
        
        // form submit
        $('#ts-prices form').unbind().submit(function(){ return false; });
        $('#ts-prices input[type=submit]').click(function(){
          if ( $('[name="ticket[manifestation_id]"][checked]').length == 1 )
          {
            manif_id = encodeURIComponent($('[name="ticket[manifestation_id]"][checked]').attr('name'))+'='+encodeURIComponent($('[name="ticket[manifestation_id]"][checked]').val());
            gauge_id = encodeURIComponent('ticket[gauge_id]')+'='+encodeURIComponent($('[name="ticket[manifestation_id]"][checked]').closest('li').find('[name="ticket[gauge_id]"]').val());
            price_name = encodeURIComponent('ticket[price_name]')+'='+encodeURIComponent($(this).val());
            $.post(
              $(this).closest('form').attr('action'),
              $(this).closest('form').serialize()+'&'+manif_id+'&'+gauge_id+'&'+price_name,
              function(data){
                ts_command($('[name="ticket[manifestation_id]"][checked]',data).val());
              }
            );
          }
          return false;
        });
      });
      
      // HIDING EXTRA PRICES
      function ts_prices(j = 0)
      {
        var max_display = <?php echo $config['prices_max_display'] ?>;
        var inputs = $('#ts-prices .prices_list input[type=submit].show');
        if ( inputs.length == 0 )
          inputs = $('#ts-prices .prices_list input[type=submit]');
        
        if ( j-max_display < 0 )
          $('#ts-prices .prices_list .prices-nav:first').attr('href','#0').addClass('useless');
        else
          $('#ts-prices .prices_list .prices-nav:first').attr('href','#'+(j-max_display)).removeClass('useless');
        if ( j+max_display >= inputs.length )
          $('#ts-prices .prices_list .prices-nav:last').attr('href','#'+j).addClass('useless');
        else
          $('#ts-prices .prices_list .prices-nav:last').attr('href','#'+(j+max_display)).removeClass('useless');
        
        //if ( j + max_display >= inputs.length )
        //  j = inputs.length - max_display;
        if ( j < 0 )
          j = 0;
        
        inputs.hide();
        for ( i = j ; i < j + max_display ; i++ )
          inputs.eq(i).show();
      }
    --></script>
    <div id="ts-prices">
      <?php include_partial('ticket_prices',array('transaction' => $transaction, 'prices' => $prices, 'remove_manifestations_list' => true, )) ?>
    </div>

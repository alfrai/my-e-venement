<?php include_partial('assets') ?>

<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit no-user-select" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Partial printing') ?></h1>
    <p style="display: none;" id="global_transaction_id"><?php echo $transaction->id ?></p>
  </div>
  <?php foreach ( $manifestations as $manifestation ): ?>
  <form action="<?php echo url_for('ticket/print') ?>" method="post" target="_blank" class="partial-print print manifestations_list ui-widget-content ui-corner-all">
    <h2 class="fg-toolbar ui-widget-header ui-corner-all">
      <?php echo $manifestation ?>
      <input type="hidden" name="id" value="<?php echo $transaction_id ?>" />
      <input type="hidden" name="toprint" value="0" />
    </h2>
    <p class="prices"><?php foreach ( $manifestation->Tickets as $ticket ): ?>
      <span class="ticket ticket_prices <?php echo $ticket->printed_at || $ticket->integrated_at ? 'done' : 'todo'?>" title="#<?php echo $ticket->id ?>">
        <?php echo $ticket->price_name ?>
        <input type="hidden" name="toprint[]" value="<?php echo $ticket->id ?>" disabled="disabled" />
      </span>
    <?php endforeach ?></p>
    <p class="submit">
      <input type="submit" name="s" value="<?php echo __('Print') ?>" />
      <input type="submit" name="integrate" value="<?php echo __('Integrate') ?>" />
      <input type="submit" name="all" value="<?php echo __('Toggle tickets') ?>" class="all" />
      <span class="tickets"><?php echo __('You have already selected %%nb%% ticket(s)',array('%%nb%%' => '<span class="nb">0</span>')) ?></span>
    </p>
  </form>
  <?php endforeach ?>
  <script type="text/javascript">
      $(document).ready(function(){
        // select the tickets to print
        $('form.print .prices .ticket.todo').click(function(e){
          if ( !e.shiftKey )
            $('.ticket.last-click').removeClass('last-click');
          
          if ( $('.ticket.last-click').length == 0 )
          {
            // no other clicked element
            first = $(this);
            $(this).addClass('last-click').addClass('last');
          }
          else if ( $('.ticket.last-click input').val() > $(this).find('input').val() )
          {
            // if clicked on an element on the left of the last one
            first = $(this);
            if ( $('.ticket.last-click').hasClass('selected') )
              $('.ticket.last-click').prev().addClass('last');
            else
              $('.ticket.last-click').addClass('last');
          }
          else
          {
            // if clicked on an element on the right of the last one
            if ( $('.ticket.last-click').hasClass('selected') )
              first = $('.ticket.last-click').next();
            else
              first = $('.ticket.last-click');
            $(this).addClass('last');
          }
          
          $i = 0;
          for ( tck = first ; true ; tck = tck.next() )
          {
            if ( tck.find('input[disabled]').length > 0 )
            {
              tck.addClass('selected');
              tck.find('input').removeAttr('disabled');
            }
            else if ( $i == 0 && tck.hasClass('last') ) // only if clicked on a single ticket
            {
              tck.removeClass('selected');
              tck.find('input').attr('disabled','disabled');
            }
            
            $i++;
            
            // leaving the loop
            if ( tck.hasClass('last') )
            {
              $('.ticket.last').removeClass('last');
              break;
            }
            
            // get out of the loop if no more element
            if ( tck.next().length == 0 )
            {
              $('.ticket.last').removeClass('last');
              break;
            }
          }
          
          $('.last-click, .last').removeClass('last-click').removeClass('last');
          $(this).addClass('last-click');
          $('.submit .tickets .nb').html($('.ticket.selected').length)
        });
        
        // select all
        $('form.print .submit [name=all]').click(function(){
          $('form.print .ticket [name="toprint[]"]').click();
          return false;
        });
        
        // integrate instead of printing
        $('form.print [name=integrate]').click(function(){
          form = $(this).closest('form.print');
          form.attr('action',"<?php echo url_for('ticket/integrate') ?>");
          return true;
        });
        
        // close the window during printing
        $('form.print').submit(function(){
          setTimeout(function(){ window.close(); },2000);
        });
      });
  </script>
  <form action="javascript: window.close()" class="close" method="get">
    <p><input type="submit" name="close" value="<?php echo __('Close',array(),'menu') ?>" /></p>
  </form>
</div>

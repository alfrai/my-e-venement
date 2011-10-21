<?php include_partial('assets') ?>

<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Partial printing') ?></h1>
    <p style="display: none;" id="global_transaction_id"><?php echo $transaction->id ?></p>
  </div>
  <?php foreach ( $manifestations as $manifestation ): ?>
  <!--<form action="<?php echo url_for('ticket/print?id='.$transaction_id) ?>" method="get" target="_blank" class="partial-print print manifestations_list ui-widget-content ui-corner-all">-->
  <form action="<?php echo url_for('ticket/print') ?>" method="post" target="_blank" class="partial-print print manifestations_list ui-widget-content ui-corner-all">
    <h2 class="fg-toolbar ui-widget-header ui-corner-all">
      <?php echo $manifestation ?>
      <input type="hidden" name="id" value="<?php echo $transaction_id ?>" />
    </h2>
    <p class="prices"><?php foreach ( $manifestation->Tickets as $ticket ): ?>
      <span class="ticket <?php echo $ticket->printed || $ticket->integrated ? 'done' : 'todo'?>" title="#<?php echo $ticket->id ?>">
        <?php echo $ticket->price_name ?>
        <input type="hidden" name="toprint[]" value="<?php echo $ticket->id ?>" disabled="disabled" />
      </span>
    <?php endforeach ?></p>
    <p class="submit"><input type="submit" name="s" value="<?php echo __('Print') ?>" /></p>
  </form>
  <?php endforeach ?>
  <script type="text/javascript">
      $(document).ready(function(){
        $('form.print .prices .ticket.todo').click(function(){
          if ( $(this).find('input[disabled]').length > 0 )
          {
            $(this).addClass('selected');
            $(this).find('input').removeAttr('disabled');
          }
          else
          {
            $(this).removeClass('selected');
            $(this).find('input').attr('disabled','disabled');
          }
        });
        $('form.print').submit(function(){
          setTimeout(function(){ window.close(); },2000);
        });
      });
  </script>
  <form action="javascript: window.close()" class="close" method="get">
    <p><input type="submit" name="close" value="<?php echo __('Close',array(),'menu') ?>" /></p>
  </form>
</div>

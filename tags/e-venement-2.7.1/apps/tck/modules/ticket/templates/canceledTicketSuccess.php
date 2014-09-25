<div class="ui-widget-content ui-corner-all passed" id="cancel-tickets">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Ticket canceled.') ?></h1>
  </div>
  <p class="link ui-corner-all"><?php echo link_to(__('Get back for a new cancellation...'),'ticket/cancel?pay='.$transaction->id) ?></p>
  <p class="print" style="display: none"><?php echo link_to(__('Print ticket'),'ticket/print?id='.$transaction->id,array('target' => '_blank')) ?></p>
  <script type="text/javascript">
    $(document).ready(function(){
      <?php if ( sfConfig::get('app_tickets_auto_print') ): ?>
      window.open($('#cancel-tickets .print a').attr('href'));
      <?php endif ?>
      setTimeout(function(){
        document.location = $('#cancel-tickets .link a').attr('href');
      },1000);
    });
  </script>
</div>

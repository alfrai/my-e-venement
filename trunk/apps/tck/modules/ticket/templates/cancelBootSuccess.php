<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all" id="cancel-tickets">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Cancelling tickets') ?></h1>
  </div>
  <form action="" method="get" class="ui-widget-content ui-corner-all">
    <p>
      <label for="ticket_id"><?php echo __('Ticket') ?></label>
      #<input type="text" style="width: 120px" name="ticket_id" value="" />
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="" value="<?php echo __('cancel') ?>" />
    </p>
  </form>
  <form action="<?php echo url_for('ticket/pay') ?>" method="get" class="ui-widget-content ui-corner-all pay">
    <p>
      <label for="id">Pay back for</label>
      #<input type="text" name="id" value="<?php echo $pay ?>" />
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="" value="<?php echo __('pay') ?>" />
    </p>
  </form>
</div>
<script type="text/javascript">
  $(document).ready(function(){
    $('form input[name=ticket_id]').focus();
    setTimeout(function(){
      $('.sf_admin_flashes').fadeOut();
    },4000);
  });
</script>

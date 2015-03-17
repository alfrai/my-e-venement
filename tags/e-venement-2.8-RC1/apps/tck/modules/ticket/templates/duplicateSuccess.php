<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all" id="cancel-tickets">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Duplicate tickets',null,'menu') ?></h1>
  </div>
  <form action="" method="get" class="ui-widget-content ui-corner-all" target="_blank">
    <p>
      <label for="ticket_id"><?php echo __('Ticket') ?></label>
      #<input type="text" style="width: 120px" name="ticket_id" value="" title="ex: 289,401-407,512" />
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="submit" value="<?php echo __('duplicate') ?>" />
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

<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>


<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all action">
    <h1><?php echo __('Respawn a transaction',array(),'menu') ?></h1>
  </div>
  <form class="ui-corner-all ui-widget-content action"
        id="operation"
        action="<?php echo url_for('ticket/access') ?>"
        method="post">
    <p>
      <input type="text" name="id" value="<?php echo $transaction_id ?>" />
      <input type="checkbox" name="reopen"
             value="true" title="<?php echo __('Unlock this transaction (only granted users).') ?>" />
      <input type="submit" name="ok" value="ok" />
    </p>
  </form>
  <script type="text/javascript">
    $(document).ready(function(){
      $('#operation input[type=text]').focus();
    });
  </script>
</div>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __("Ticket's log",null,'menu') ?></h1>
  </div>

<?php include_partial('global/flashes') ?>

<form class="ui-corner-all ui-widget-content action" action="<?php echo url_for('ticket/show') ?>" method="get">
  <p>
    <input type="text" name="id" value="" autocomplete="off" />
    <input type="submit" value="<?php echo __('Show',null,'sf_admin') ?>" name="submit" />
    <script type="text/javascript"><!--
      $(document).ready(function(){
        $('input[type=text]:first-child').focus();
      });
    --></script>
  </p>
</form>

</div>

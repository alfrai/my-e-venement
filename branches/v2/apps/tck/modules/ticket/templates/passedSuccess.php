<div class="ui-widget-content ui-corner-all passed" id="checkpoint">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Checkpoint passed.') ?></h1>
  </div>
  <p class="link ui-corner-all"><?php echo link_to(__('Get back for a new ticket...'),'ticket/control') ?></p>
  <script type="text/javascript">
    $(document).ready(function(){
      setTimeout(function(){
        document.location = $('#checkpoint .link a').attr('href');
      },2000);
    });
  </script>
</div>

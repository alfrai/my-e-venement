<div class="ui-widget-content ui-corner-all failed" id="checkpoint">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Checkpoint failed !') ?></h1>
  </div>
  <p class="link ui-corner-all"><?php echo link_to(__('Try again...'),'ticket/control') ?></p>
  <script type="text/javascript">
    $(document).ready(function(){
      setTimeout(function(){
        document.location = $('#checkpoint .link a').attr('href');
      },2500);
    });
  </script>
</div>

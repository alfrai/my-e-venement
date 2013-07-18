<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all success" id="integrate">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Tickets integrated!') ?></h1>
  </div>
  <p class="link ui-corner-all"><?php echo __('Your tickets have been integrated correctly into your ticketing system...') ?></p>
  <script type="text/javascript">
    $(document).ready(function(){
      setTimeout(function(){
        window.close();
      },2500);
    });
  </script>
</div>

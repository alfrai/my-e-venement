<div class="ui-widget-content ui-corner-all failed" id="checkpoint">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Checkpoint failed !') ?></h1>
    <?php if ( count($errors) > 0 ): ?>
    <?php endif ?>
  </div>
  <div class="link ui-corner-all">
    <ul>
      <?php foreach ( $errors as $e ): ?>
      <li><?php echo $e ?></li>
      <?php endforeach ?>
    </ul>
    <p><?php echo link_to(__('Try again...'),'ticket/control') ?></p>
  </div>
  <?php $delays = sfConfig::get('app_control_delays') ?>
  <script type="text/javascript">
    $(document).ready(function(){
      setTimeout(function(){
        document.location = $('#checkpoint .link a').attr('href');
      },<?php echo isset($delays['failure']) ? intval($delays['failure']) : '2.5' ?>*1000);
    });
  </script>
</div>

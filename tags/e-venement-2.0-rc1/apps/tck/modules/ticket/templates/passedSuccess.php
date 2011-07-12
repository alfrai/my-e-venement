<div class="ui-widget-content ui-corner-all passed" id="checkpoint">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Checkpoint passed.') ?></h1>
  </div>
  <div class="link ui-corner-all">
    <p><?php echo link_to(__('Get back for a new ticket...'),'ticket/control') ?></p>
    <?php if ( $comment ): ?><p class="ui-widget-content ui-corner-all comment"><?php echo $comment ?></p><?php endif ?>
  </div>
  <script type="text/javascript">
    $(document).ready(function(){
      if ( !$('.comment').html() )
      setTimeout(function(){
        document.location = $('#checkpoint .link a').attr('href');
      },1500);
    });
  </script>
</div>

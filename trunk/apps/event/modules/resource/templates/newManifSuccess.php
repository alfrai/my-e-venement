<?php include_partial('global/flashes') ?>
<?php include_partial('assets') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __("New transitional event's name:") ?></h1>
  </div>
  <form action="<?php echo url_for('resource/newManif') ?>" method="get" class="ui-widget-content ui-corner-all ui-widget batch">
    <p>
      <input type="text" name="event_name" value="" />
      <?php foreach ( $ids as $id ): ?>
        <input type="hidden" name="ids[]" value="<?php echo $id ?>" />
      <?php endforeach ?>
    </p>
    <p>
      <input type="submit" name="submit" value="<?php echo __('Ok',null,'sf_admin') ?>" />
      <input type="submit" name="cancel" value="<?php echo __('Cancel',null,'sf_admin') ?>" onclick="javascript: history.back(); return false;" />
    </p>
  </form>
  <script type="text/javascript"><!--
    $('form').submit(function(){
      if ( !$(this).find('[name=event_name]').val() )
      {
        alert("<?php echo __('The item has not been saved due to some errors.', null, 'sf_admin') ?>");
        setTimeout(function(){ $('#transition .close').click(); }, 250);
        return false;
      }
    });
  --></script>
</div>

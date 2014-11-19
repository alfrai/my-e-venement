<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __("Ticket's log",null,'menu') ?></h1>
  </div>

<?php include_partial('global/flashes') ?>

<form class="ui-corner-all ui-widget-content action by-id" action="<?php echo url_for('ticket/show') ?>" method="get">
  <p>
    #<input type="text" name="id" value="" autocomplete="off" />
    <input type="submit" value="<?php echo __('Show',null,'sf_admin') ?>" name="submit" />
    <script type="text/javascript"><!--
      $(document).ready(function(){
        $('input[type=text]:first-child').focus();
      });
    --></script>
  </p>
</form>

<form class="ui-corner-all ui-widget-content action by-seat" action="<?php echo url_for('ticket/show') ?>" method="get">
  <p class="seat_name">
    <label><?php echo __('Seat') ?>:</label>
    <input type="text" name="seat_name" value="" />
  </p>
  <?php foreach ( $manifestation->getJavascripts() as $js  ) use_javascript($js) ?>
  <?php foreach ( $manifestation->getStylesheets() as $css => $media ) use_stylesheet($css) ?>
  <p class="manifestation_id">
    <label><?php echo __('Manifestation') ?>:</label>
    <?php echo $manifestation->getRawValue()->render('manifestation_id') ?>
  </p>
  <input type="submit" value="<?php echo __('Show',null,'sf_admin') ?>" name="submit" />
</form>

</div>

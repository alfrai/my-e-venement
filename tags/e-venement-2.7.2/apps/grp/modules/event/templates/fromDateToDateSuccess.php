<?php use_helper('I18N', 'Date') ?>
<?php include_partial('event/assets') ?>

<div id="sf_admin_container">
  <?php include_partial('event/flashes') ?>

  <div id="sf_admin_content">
    <div class="fg-toolbar ui-widget-header ui-corner-top">
      <h1>
        <?php echo __('By event', null, 'menu') ?>
        -
        <?php echo __('From date to date') ?>
      </h1>
    </div>
    <div class="ui-widget-content ui-corner-bottom from-date-to-date">
      <form action="<?php echo url_for('event/fromDateToDate') ?>" method="post">
        <?php echo $form ?>
        <button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" value="refused" name="submit"><?php echo __('Extract refused') ?></button>
        <button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" value="accepted" name="submit"><?php echo __('Extract accepted') ?></button>
      </form>
    </div>
  </div>

  <?php include_partial('event/themeswitcher') ?>
</div>

<?php use_helper('I18N', 'Date') ?>
<?php include_partial('event/assets') ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Entry %%name%%', array('%%name%%' => $event->getName()), 'messages') ?></h1>
  </div>

  <?php include_partial('event/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('event/form_header', array('event' => $event, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('event/form', array('entry' => $entry, 'event' => $event, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('event/form_footer', array('event' => $event, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <?php include_partial('event/themeswitcher') ?>
</div>

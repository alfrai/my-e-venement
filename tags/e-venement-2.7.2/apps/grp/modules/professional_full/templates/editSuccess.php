<?php use_helper('I18N', 'Date') ?>
<?php include_partial('assets') ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('%%name%%, %%professional%%', array('%%name%%' => (string)$professional->Contact, '%%professional%%' => (string)$professional), 'messages') ?></h1>
  </div>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('form_header', array('professional' => $professional, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('form_edit', array('professional' => $professional, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('form_footer', array('professional' => $professional, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <?php include_partial('themeswitcher') ?>
</div>

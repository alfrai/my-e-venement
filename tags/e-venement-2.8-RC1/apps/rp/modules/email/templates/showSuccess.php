<?php use_helper('I18N', 'Date') ?>
<?php include_partial('email/assets') ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('View Email', array(), 'messages') ?></h1>
  </div>

  <?php include_partial('global/flashes') ?>

  <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('email/show_actions', array('form' => $form, 'email' => $email, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div class="ui-helper-clearfix"></div>

  <?php include_partial('show', array('form' => $form, 'email' => $email, 'configuration' => $configuration)) ?>

  <?php include_partial('email/themeswitcher') ?>
</div>

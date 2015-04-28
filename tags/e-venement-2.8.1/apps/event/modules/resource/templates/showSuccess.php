<?php use_helper('I18N', 'Date') ?>
<?php include_partial('location/assets') ?>

<div id="sf_admin_container" class="sf_admin_show ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('View Resource %%name%%', array('%%name%%' => (string)$location), 'messages') ?></h1>
  </div>

  <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('location/show_actions', array('form' => $form, 'location' => $location, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div class="ui-helper-clearfix"></div>

  <?php include_partial('show', array('form' => $form, 'location' => $location, 'configuration' => $configuration)) ?>

  <?php include_partial('show_footer', array('location' => $location, 'form' => $form, 'configuration' => $configuration)) ?>

  <?php include_partial('location/themeswitcher') ?>
</div>

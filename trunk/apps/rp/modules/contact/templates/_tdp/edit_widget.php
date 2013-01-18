<?php use_helper('I18N', 'Date') ?>
<?php include_partial('contact/assets') ?>

<div id="tdp-content">
<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Contact %%name%% %%firstname%%', array('%%name%%' => $contact->getName(), '%%firstname%%' => $contact->getFirstname()), 'messages') ?></h1>
  </div>

  <?php include_partial('contact/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('contact/form_header', array('contact' => $contact, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('contact/form', array('contact' => $contact, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('contact/form_footer', array('contact' => $contact, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <?php include_partial('contact/themeswitcher') ?>
</div>
</div>

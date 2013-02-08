<?php use_helper('Number') ?>
<div id="tdp-side-bar" class="tdp-container object">
  <?php if ( $sf_user->hasCredential('tck-overview') ): ?>
  <div class="tdp-side-widget" id="tdp-side-ticketting">
    <h2><?php echo __('Ticketting') ?></h2>
    <?php include_partial('contact/tdp/side_widget_object_events',array('object' => $object, 'config' => $config,)) ?>
  </div>
  <?php endif ?>
  <?php if ( $sf_user->hasCredential('pr-group') ): ?>
  <div class="tdp-side-widget" id="tdp-side-groups">
    <h2><?php echo __('Groups') ?></h2>
    <?php include_partial('contact/tdp/side_widget_object_groups',array('object' => $object, 'config' => $config,)) ?>
  </div>
  <?php endif ?>
  <?php if ( $sf_user->hasCredential('pr-emailing') ): ?>
  <div class="tdp-side-widget" id="tdp-side-emailings">
    <h2><?php echo __('Emailings') ?></h2>
    <?php include_partial('contact/tdp/side_widget_object_emails',array('object' => $object, 'config' => $config,)) ?>
  </div>
  <?php endif ?>
</div>

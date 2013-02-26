<?php use_helper('Number') ?>
<div id="tdp-side-bar" class="tdp-container object ui-widget-content ui-corner-all">
  <?php foreach ( $config['side_properties'] as $name => $widget ): ?>
  <?php if ( $sf_user->hasCredential($widget['credential']) ): ?>
  <div class="tdp-side-widget" id="tdp-side-<?php strtolower($name) ?>">
    <h2><?php echo __($name) ?></h2>
    <?php include_partial($widget['partial'],array('object' => $object, 'config' => $config,)) ?>
  </div>
  <?php endif ?>
  <?php endforeach ?>
</div>

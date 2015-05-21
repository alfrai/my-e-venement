<div class="sf_admin_fake_flashes ui-widget">
<?php if (isset($error)): ?>
  <div class="error ui-state-error ui-corner-all">
    <span class="ui-icon ui-icon-alert floatleft"></span>&nbsp;
    <?php echo __($error, array(), 'sf_admin') ?>
  </div>
<?php endif; ?>

<?php if (isset($notice)): ?>
  <div class="notice ui-state-highlight ui-corner-all">
    <span class="ui-icon ui-icon-info floatleft"></span>&nbsp;
    <?php echo __($notice, array(), 'sf_admin') ?>
  </div>
<?php endif; ?>

<?php if (isset($success)): ?>
  <div class="success ui-state-success ui-corner-all">
    <span class="ui-icon ui-icon-circle-check floatleft"></span>&nbsp;
    <?php echo __($success, array(), 'sf_admin') ?>
  </div>
<?php endif; ?>

</div>

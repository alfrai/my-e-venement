<div class="sf_admin_flashes ui-widget">
<?php if ($sf_user->hasFlash('error')): ?>
  <div class="success ui-state-error ui-corner-all">
    <span class="ui-icon ui-icon-alert floatleft"></span>&nbsp;
    <?php echo __($sf_user->getFlash('error')) ?>
  </div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="notice ui-state-highlight ui-corner-all">
    <span class="ui-icon ui-icon-info floatleft"></span>&nbsp;
    <?php echo __($sf_user->getFlash('notice')) ?>
  </div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('success')): ?>
  <div class="success ui-state-success ui-corner-all">
    <span class="ui-icon ui-icon-circle-check floatleft"></span>&nbsp;
    <?php echo __($sf_user->getFlash('success')) ?>
  </div>
<?php endif; ?>

</div>

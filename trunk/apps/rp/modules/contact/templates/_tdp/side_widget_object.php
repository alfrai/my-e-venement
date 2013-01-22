<div id="tdp-side-bar" class="tdp-container object">
  <?php if ( $sf_user->hasCredential('tck-overview') ): ?>
  <div class="tdp-side-widget" id="tdp-side-ticketting">
    <h2><?php echo __('Ticketting') ?></h2>
  </div>
  <?php endif ?>
  <?php if ( $sf_user->hasCredential('pr-group-common') || $sf_user->hasCredential('pr-group-common') ): ?>
  <div class="tdp-side-widget" id="tdp-side-groups">
    <h2><?php echo __('Groups') ?></h2>
  </div>
  <?php endif ?>
  <?php if ( $sf_user->hasCredential('pr-emailing') ): ?>
  <div class="tdp-side-widget" id="tdp-side-emailings">
    <h2><?php echo __('Emailings') ?></h2>
  </div>
  <?php endif ?>
</div>

<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php $manifs[$manif->happens_at.'-'.$manif->id] = $manif ?>
<?php endforeach ?>
<?php
  if ( sfConfig::get('app_listing_manif_date','DESC') == 'DESC' )
    krsort($manifs);
  else
    ksort($manifs);
?>
<?php foreach($manifs as $manif): ?>
  <?php if ( $manif->ExtraInformations->count() > 0 || trim($manif->description) ): ?>
  <span title="<?php echo __('Extra informations') ?>" class="ui-icon ui-icon-alert floatright"></span>
  <?php endif ?>
  <br/>
<?php endforeach ?>


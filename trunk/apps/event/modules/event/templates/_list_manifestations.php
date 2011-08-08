<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php $manifs[$manif->happens_at] = link_to(format_date($manif->happens_at,'d MMM yyyy HH:mm'),'manifestation/'.($sf_user->hasCredential('event-manif-edit') ? 'edit' : 'show').'?id='.$manif->id,array('title' => '@ '.$manif->Location)) ?>
<?php endforeach ?>
<?php sort($manifs) ?>
<?php echo implode('<br/>',$manifs) ?>

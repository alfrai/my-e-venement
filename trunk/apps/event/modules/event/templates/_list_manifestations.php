<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php
    $manifs[$manif->happens_at.'-'.$manif->id] = link_to(format_date($manif->happens_at,'EEE d MMM yyyy HH:mm'),'manifestation/show?id='.$manif->id,array('title' => '@ '.$manif->Location, 'style' =>  'background-color: #'.$manif->Color->color.';'))
      .' <a title="'.__('Sell').'" href="'.cross_app_url_for('tck','ticket/sell#manif-'.$manif->id).'"><span class="ui-icon-li ui-icon ui-icon-microplus"></span></a>'
  ?>
<?php endforeach ?>
<?php ksort($manifs) ?>
<?php echo implode('<br/>',$manifs) ?>

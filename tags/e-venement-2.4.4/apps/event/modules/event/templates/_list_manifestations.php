<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php
    $manifs[$manif->happens_at.'-'.$manif->id] =
      link_to(format_date($manif->happens_at,'EEE d MMM yyyy HH:mm'),
        'manifestation/show?id='.$manif->id,
        array('title' => format_date(strtotime($manif->happens_at) + strtotime($manif->duration_h_r) - strtotime('0:00'), 'EEE d MMM yyyy HH:mm').' @ '.$manif->Location, 'style' =>  $manif->Color ? 'background-color: #'.$manif->Color->color.';' : '')
      ).' <a title="'.__('Sell').'" href="'.cross_app_url_for('tck','ticket/sell#manif-'.$manif->id).'"><span class="ui-icon-li ui-icon ui-icon-microplus"></span></a>'
  ?>
<?php endforeach ?>
<?php ksort($manifs) ?>
<?php echo implode('<br/>',$manifs) ?>

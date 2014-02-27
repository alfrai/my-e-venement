<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php
    $manifs[$manif->happens_at.'-'.$manif->id] =
      link_to(format_date($manif->happens_at,'EEE d MMM yyyy HH:mm'),
        'manifestation/show?id='.$manif->id,
        array('title' => '-> '.format_date(strtotime($manif->ends_at), 'EEE d MMM yyyy HH:mm').' @ '.$manif->Location, 'style' => 'background-color: '.$manif->Color.';')
      ).' <a class="sell" title="'.__('Sell').'" href="'.cross_app_url_for('tck','ticket/sell#manif-'.$manif->id).'"><span class="ui-icon-li ui-icon ui-icon-microplus"></span></a>'
  ?>
<?php endforeach ?>
<?php
  if ( sfConfig::get('app_listing_manif_date','DESC') == 'DESC' )
    krsort($manifs);
  else
    ksort($manifs);
?>
<?php echo implode('<br/>',$manifs) ?>

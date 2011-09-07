<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php
    $manifs[$manif->happens_at] = link_to(format_date($manif->happens_at,'d MMM yyyy HH:mm'),'manifestation/show?id='.$manif->id,array('title' => '@ '.$manif->Location))
      .' <a title="'.__('Sell').'" href="'.cross_app_url_for('tck','ticket/sell#manif-'.$manif->id).'"><span class="ui-icon-li ui-icon ui-icon-microplus"></span></a>'
  ?>
<?php endforeach ?>
<?php sort($manifs) ?>
<?php echo implode('<br/>',$manifs) ?>

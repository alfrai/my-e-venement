<ul class="ui-widget ui-widget-content ui-corner-all manifestations-list">
  <?php foreach ( $manifestations as $manif ): ?>
  <li style="<?php echo $manif->color_id ? 'background-color: '.$manif->Color : '' ?>">
    <?php echo link_to($manif, 'manifestation/show?id='.$manif->id) ?>
  </li>
  <?php endforeach ?>
</ul>

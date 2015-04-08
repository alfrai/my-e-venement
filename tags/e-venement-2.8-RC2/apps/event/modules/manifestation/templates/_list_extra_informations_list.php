  <ul>
    <?php if ( $manifestation->ExtraInformations->count() > 0 ): ?>
    <?php foreach ( $manifestation->ExtraInformations as $info ): ?>
    <li><?php echo image_tag( $info->checked ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?> <?php echo $info ?></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>

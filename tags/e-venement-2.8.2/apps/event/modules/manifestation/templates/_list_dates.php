<div <?php if ( $manifestation->color_id ): ?>style="background-color: <?php echo $manifestation->Color ?>"<?php endif ?>>
  <a href="<?php echo url_for('manifestation/show?id='.$manifestation->id) ?>" class="from"><?php echo $manifestation->getShortenedDate() ?></a>
  <a href="<?php echo url_for('manifestation/show?id='.$manifestation->id) ?>" class="to"><?php echo format_date($manifestation->ends_at, 'EEE d MMM yyyy HH:mm') ?></a>
</div>

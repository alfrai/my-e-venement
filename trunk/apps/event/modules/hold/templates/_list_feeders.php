<?php $arr = array(); ?>
<?php foreach ( $hold->Feeders as $feeder ): ?>
  <?php $arr[] = link_to($feeder, 'hold/edit?id='.$feeder->id) ?>
<?php endforeach ?>

<?php if ( count($arr) > 0 ): ?>
<?php implode(', ', $arr) ?>
<?php else: ?>
-
<?php endif ?>

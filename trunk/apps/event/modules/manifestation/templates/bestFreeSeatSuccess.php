<?php if ( count($manifestations) > 0 ): ?>
<ol>
<?php foreach ( $manifestations as $manifestation ): ?>
  <?php include_partial('best_free_seat', array('manifestation' => $manifestation)) ?>
<?php endforeach ?>
</ol>
<?php endif ?>

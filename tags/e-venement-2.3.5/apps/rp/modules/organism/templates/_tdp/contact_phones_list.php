<?php if ( $object->Contact ): ?>
<?php if ( $object->Contact->Phonenumbers->count() > 0 ): ?>
<ul class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<?php foreach ( $object->Contact->Phonenumbers as $pn ): ?>
  <li><?php echo $pn ?>: <?php echo $pn->number ?></li>
<?php endforeach ?>
</ul>
<?php endif ?>
<?php endif ?>

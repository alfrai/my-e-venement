<?php if ( $object->Organism ): ?>
<a href="mailto:<?php echo $object->Organism->email ?>" class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<?php echo $object->Organism->email ?>
</a>
<?php endif ?>

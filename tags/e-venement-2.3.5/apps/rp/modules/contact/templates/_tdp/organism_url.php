<?php if ( $object->Organism ): ?>
<a href="<?php echo $object->Organism->url ?>" class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<?php echo $object->Organism->url ?>
</a>
<?php endif ?>

<?php if ( $object->Organism ): ?>
<span class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<label><?php echo __("Website") ?></label>
<a href="<?php echo $object->Organism->url ?>">
<?php echo $object->Organism->url ?>
</a>
</span>
<?php endif ?>

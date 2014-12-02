<?php if ( $object->Organism ): ?>
<span class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<label><?php echo __("Organism's email") ?></label>
<a href="mailto:<?php echo $object->Organism->email ?>">
<?php echo $object->Organism->email ?>
</a>
</span>
<?php endif ?>

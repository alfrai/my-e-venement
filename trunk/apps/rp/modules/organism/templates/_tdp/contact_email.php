<?php if ( $object->Contact && $object->Contact->email ): ?>
<a href="mailto:<?php echo $object->Contact->email ?>" class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<?php echo $object->Contact->email ?>
</a>
<?php endif ?>

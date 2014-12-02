<?php if ( $object->Contact && $object->Contact->email ): ?>
<label><?php echo __('Personal email address') ?></label>
<a href="mailto:<?php echo $object->Contact->email ?>" class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>">
<?php echo $object->Contact->email ?>
</a>
<?php endif ?>

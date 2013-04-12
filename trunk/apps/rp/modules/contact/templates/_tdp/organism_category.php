<?php if ( $object->Organism ): ?>
<span class="tdp-<?php echo str_replace('.php','',basename(__FILE__)) ?>" title="<?php echo __("Organism's category") ?>">
  <?php echo $object->Organism->Category ?>
</span>
<?php endif ?>

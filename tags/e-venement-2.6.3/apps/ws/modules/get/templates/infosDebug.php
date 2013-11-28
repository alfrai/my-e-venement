<?php if ( isset($content) ): ?>
<?php print_r($content->getRaw('events')) ?>
<?php echo json_encode($content->getRaw('events')) ?>
<?php endif ?>

<?php if ( isset($content) ): ?>
<?php echo json_encode($content->getRaw('events')) ?>
<?php endif ?>

<?php if ( $hold->next ): ?>
<?php echo link_to($hold->Next, 'hold/edit?id='.$hold->next) ?>
<?php else: ?>
-
<?php endif ?>

<?php if ( sfConfig::get('sf_debug') ): ?>
<pre><?php print_r($sf_data->getRaw('contacts')) ?></pre>
<?php else: ?>
<?php echo json_encode($contacts) ?>
<?php endif ?>

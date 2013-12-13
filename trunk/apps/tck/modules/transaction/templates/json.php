<?php if ( sfConfig::get('sf_debug') ): ?>
<pre><?php print_r($sf_data->getRaw('json')) ?></pre>
<?php else: ?>
<?php echo json_encode($sf_data->getRaw('json')) ?>
<?php endif ?>

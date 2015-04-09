<?php if ( sfConfig::get('sf_debug') ): ?>
<pre><?php print_r($sf_data->getRaw('declinations')) ?></pre>
<?php else: ?>
<?php echo json_encode($declinations) ?>
<?php endif ?>

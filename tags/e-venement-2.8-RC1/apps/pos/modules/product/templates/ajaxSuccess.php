<?php if ( sfConfig::get('sf_debug') ): ?>
<pre><?php print_r($sf_data->getRaw('products')) ?></pre>
<?php else: ?>
<?php echo json_encode($products) ?>
<?php endif ?>

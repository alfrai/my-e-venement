<?php if ( sfConfig::get('sf_web_config', false) ): ?>
<?php print_r($json->getRawValue()) ?>
<?php else: ?>
<?php echo json_encode($json->getRawValue()) ?>
<?php endif ?>

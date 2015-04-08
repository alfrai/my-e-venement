<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
<pre>
<?php print_r($data->getRawValue()) ?>
</pre>
<?php else: ?>
<?php include_partial('data_'.$type, array('data' => $data)) ?>
<?php endif ?>

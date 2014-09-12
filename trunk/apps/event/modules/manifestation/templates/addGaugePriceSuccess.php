<?php $json = $sf_data->getRaw('json') ?>
<?php foreach ( $json as $type => $message ) $json[$type] = __($message); ?>

<?php if ( !sfConfig::get('sf_web_debug', false) ): ?>
  <?php echo json_encode($json) ?>
<?php else: ?>
<pre>
  <?php print_r($json) ?>
</pre>
<?php endif ?>

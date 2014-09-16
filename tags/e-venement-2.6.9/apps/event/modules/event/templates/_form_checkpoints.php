<script type="text/javascript">
  var event_checkpoint_list_url = '<?php echo url_for('checkpoint/batchEdit?id='.$form->getObject()->id) ?>';
  var event_checkpoint_new_url  = '<?php echo url_for('checkpoint/new?event='.$form->getObject()->slug) ?>';
  var event_organism_ajax_url   = '<?php echo cross_app_url_for('rp','organism/ajax') ?>';
</script>
<div class="checkpoints checkpoint_list"></div>
<div class="checkpoints checkpoint_new ui-widget-content ui-corner-all">
<div class="checkpoints _delete_csrf_token" style="display:none;"><?php $form = new BaseForm(); echo $form->getCSRFToken() ?></div>
</div>

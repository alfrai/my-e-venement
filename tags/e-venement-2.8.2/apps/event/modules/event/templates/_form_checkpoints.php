<script type="text/javascript">
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  LI.event_checkpoint_list_url = $('.checkpoints.checkpoint_list').attr('data-url');
  LI.event_checkpoint_new_url  = $('.checkpoints.checkpoint_new').attr('data-url');
  LI.event_organism_ajax_url   = '<?php echo cross_app_url_for('rp','organism/ajax') ?>';
});
</script>
<div class="checkpoints checkpoint_list" data-url="<?php echo url_for('checkpoint/batchEdit?id='.$form->getObject()->id) ?>"></div>
<div class="checkpoints checkpoint_new ui-widget-content ui-corner-all" data-url="<?php echo url_for('checkpoint/new?event='.$form->getObject()->slug) ?>">
  <div class="checkpoints _delete_csrf_token" style="display:none;"><?php $form = new BaseForm(); echo $form->getCSRFToken() ?></div>
</div>

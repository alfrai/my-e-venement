<?php $manifestation = isset($manifestation) && !is_null($manifestation) ? $manifestation : $form->getObject(); ?>
<div class="sf_admin_form_row sf_admin_form_field_files_list">
  <div style="display:none;" id="template_lnk">
    <a title="" data-name="" data-id="" class="fg-button-mini fg-button ui-state-default fg-button-icon-left ui-priority-secondary sf_admin_form_field_file_del">
      <span class="ui-icon ui-icon-trash">&nbsp;</span>
      Supprimer
    </a>
  </div>
  <label><?php echo __('Event files') ?></label>
  <ul class="event_files">
    <?php foreach ( $manifestation->Event->Files as $file ): ?>
      <li>
        <a target="_blank" href="<?php echo $file->getUrl(); ?>"><?php echo $file->name; ?></a>
      </li>
    <?php endforeach ?>
  </ul>
  <label><?php echo __('Manifestation files') ?></label>
  <ul class="manifestation_files">
    <?php foreach ( $manifestation->Files as $file ): ?>
      <li>
        <a target="_blank" href="<?php echo $file->getUrl(); ?>"><?php echo $file->name; ?></a>
        <a title="<?php echo __('Delete').' '.$file->name; ?>" data-name="<?php echo $file->name; ?>" data-id="<?php echo $file->id; ?>" class="fg-button-mini fg-button ui-state-default fg-button-icon-left ui-priority-secondary sf_admin_form_field_file_del">
          <span class="ui-icon ui-icon-trash">&nbsp;</span>
          Supprimer
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</div>
<div class="sf_admin_form_row sf_admin_form_field_new_file">
  <label><?php echo __('Add a file') ?></label><span class="help"> <?php echo __('Maximum size: 2MB'); ?></span>
  <div class="widget">
    <input type="file" id="manifestation_file">
  </div>
</div>

<script type="text/javascript">
  var manifestation_file_url = "<?php echo url_for('manifestation_files/add'); ?>";
  var manifestation_del_url = "<?php echo url_for('manifestation_files/del'); ?>";
  var manifestation_id = <?php echo $manifestation->id; ?>;
  var confirm_msg = "<?php echo __('Delete file'); ?>";
  var del_msg = "<?php echo __('Delete').' ' ?>";
</script>

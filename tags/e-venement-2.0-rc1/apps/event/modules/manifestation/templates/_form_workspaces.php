<?php use_javascript('form-list') ?>
<?php use_stylesheet('form-list') ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_workspaces_list">
  <div class="label ui-helper-clearfix">
    <label for="manifestation_workspaces"><?php echo __('Workspaces list') ?></label>
  </div>
  <div id="form_workspaces" class="sf_admin_form_list ajax">
    <script type="text/javascript">
      document.getElementById('form_workspaces').url   = '<?php echo url_for('gauge/batchEdit?id='.$form->getObject()->id) ?>';
      document.getElementById('form_workspaces').field = '.sf_admin_form_field_value';
    </script>
  </div>
</div>

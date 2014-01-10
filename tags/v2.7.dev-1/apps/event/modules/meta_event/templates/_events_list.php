<?php use_javascript('meta-event') ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_name">
  <div class="label ui-helper-clearfix">
    <label for="meta_event_name"><?php echo __('Event List') ?></label>
  </div>
  <div class="events_list">
    <script type="text/javascript">var events_url = '<?php echo url_for('@event') ?>';</script>
  </div>
</div>

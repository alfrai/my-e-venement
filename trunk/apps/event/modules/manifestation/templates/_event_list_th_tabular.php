<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_date sf_admin_list_th_happens_at ui-state-default ui-th-column">
  <?php echo __('Happens at', array(), 'messages') ?>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_list_location ui-state-default ui-th-column">
  <?php echo __('Location', array(), 'messages') ?>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_list_description ui-state-default ui-th-column">
  <?php echo __('Memo', array(), 'messages') ?>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_list_gauge ui-state-default ui-th-column">
  <?php echo __('Gauge', array(), 'messages') ?>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_list_extra_informations_list ui-state-default ui-th-column">
  <?php echo __('Extra informations', array(), 'messages') ?>
</th>
<?php end_slot(); ?>
<?php if ( $sf_user->hasCredential('event-manif-edit') ): ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_list_actions ui-state-default ui-th-column">
  <?php //echo __('Actions', array(), 'messages') ?>
</th>
<?php end_slot(); ?>
<?php endif ?>
<?php include_slot('sf_admin.current_header') ?>

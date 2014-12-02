<?php //if ( !isset($sort) ) $sort = array(1 => 'asc'); if ( !isset($sort[1]) ) $sort[1] = 'asc'; ?>
<?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
    <a>
      <span class="ui-icon <?php echo ($sort[1] == 'asc' ? 'ui-icon-circle-triangle-s' : 'ui-icon-circle-triangle-n') ?>"></span>
      <?php echo __('Name', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_firstname ui-state-default ui-th-column">
    <a>
      <span class="ui-icon <?php echo ($sort[1] == 'asc' ? 'ui-icon-circle-triangle-s' : 'ui-icon-circle-triangle-n') ?>"></span>
      <?php echo __('Firstname', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_professional ui-state-default ui-th-column">
    <a>
      <?php echo __('Professional', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_organism ui-state-default ui-th-column">
    <a>
      <?php echo __('Organism', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?>

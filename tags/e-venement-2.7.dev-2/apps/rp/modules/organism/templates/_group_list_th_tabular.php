<?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
    <a>
      <span class="ui-icon <?php echo ($sort[1] == 'asc' ? 'ui-icon-circle-triangle-s' : 'ui-icon-circle-triangle-n') ?>"></span>
      <?php echo __('Name', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_postalcode ui-state-default ui-th-column">
    <a>
      <span class="ui-icon <?php echo ($sort[1] == 'asc' ? 'ui-icon-circle-triangle-s' : 'ui-icon-circle-triangle-n') ?>"></span>
      <?php echo __('Postalcode', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_city ui-state-default ui-th-column">
    <a>
      <span class="ui-icon <?php echo ($sort[1] == 'asc' ? 'ui-icon-circle-triangle-s' : 'ui-icon-circle-triangle-n') ?>"></span>
      <?php echo __('City', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?><?php slot('sf_admin.current_header') ?>
<th class="sf_admin_text sf_admin_list_th_nb_professionals ui-state-default ui-th-column">
    <a>
      <?php echo __('Professionals', array(), 'messages') ?>
    </a>
</th>
<?php end_slot(); ?>
<?php include_slot('sf_admin.current_header') ?>

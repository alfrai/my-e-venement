<div class="ui-helper-clearfix">
<ul class="sf_admin_actions_form" style="font-size: 13px;">
  <li class="sf_admin_action_list">
    <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for($sf_request->getParameter('id') ? 'event/show?id='.$sf_request->getParameter('id') : '@event') ?>">
      <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
      <?php echo __('Back to list',null,'sf_admin') ?>
    </a>
  </li>
  <li class="sf_admin_action_edit">
    <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('event/calendar') ?>">
      <span class="ui-icon ui-icon-circle-plus"></span>
      <?php echo __('Export') ?>
    </a>
  </li>
</ul>
</div>

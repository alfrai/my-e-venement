<div class="sf_admin_actions_block ui-widget">
  <ul class="sf_admin_actions_form">
    <li class="sf_admin_action_list">
      <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('event/edit?id='.$event->id) ?>">
        <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
        Liste
      </a>
    </li>
    <li class="sf_admin_action_send">
      <button class="fg-button ui-state-default fg-button-icon-left">
        <span class="ui-icon ui-icon-circle-check"></span>
        <?php echo __('Send') ?>
      </button>
    </li>
  </ul>
</div>

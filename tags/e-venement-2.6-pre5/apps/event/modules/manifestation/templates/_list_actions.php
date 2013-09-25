<ul class="sf_admin_td_actions fg-buttonset fg-buttonset-single">
  <li class="sf_admin_action_duplicate" title="<?php echo __('Duplicate') ?>">
    <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('manifestation/duplicate?id='.$manifestation->id) ?>"><?php echo __('Duplicate') ?></a>
  </li>
  <li class="sf_admin_action_periodicity" title="<?php echo __('Periodicity') ?>">
    <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('manifestation/periodicity?id='.$manifestation->id) ?>"><?php echo __('Periodicity') ?></a>
  </li>
</ul>

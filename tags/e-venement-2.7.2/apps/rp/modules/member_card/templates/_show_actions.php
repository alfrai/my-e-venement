<div class="sf_admin_actions_form" style="margin-left: 10px; float: none;">
  <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('contact/card?id='.$form->getObject()->contact_id) ?>">
    <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
    <?php echo __("Back to list",array(),'sf_admin') ?>
  </a>
  <?php echo link_to(
    UIHelper::addIcon(array('ui-icon' => 'trash')) . __('Delete','','sf_admin'),
    $helper->getUrlForAction('delete'),
    $form->getObject(),
    array(
      'class' => UIHelper::getClasses('class= fg-button ui-state-default fg-button-icon-left ui-priority-secondary delete '),
      'method' => 'delete',
      'confirm' => __('Are you sure?', array(), 'sf_admin')
    )
  ) ?>
</div>

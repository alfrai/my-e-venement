<ul class="sf_admin_actions_form">
  <li class="sf_admin_action_back">
    <?php if (method_exists($helper, 'linkTo_back')): ?>
      <?php echo $helper->linkTo_back($form->getObject(), array(  'action' => 'backToSurvey',  'label' => __('List', null, 'sf_admin').'<span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>',  'ui-icon' => 'arrowreturnthick-1-w',  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'back',)) ?>
    <?php else: ?>
      <?php echo link_to(__('Liste<span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>', array(), 'messages'), 'query/backToSurvey?id='.$survey_query->getId(), 'class= fg-button ui-state-default fg-button-icon-left ') ?>
    <?php endif; ?>
  </li>
  <?php echo $helper->linkToEdit($survey_query, array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'edit',  'label' => 'Edit',  'ui-icon' => '',)) ?>
</ul>

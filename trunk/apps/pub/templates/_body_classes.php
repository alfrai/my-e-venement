<?php echo
  'app-'.$sf_context->getConfiguration()->getApplication().' '.
  'mod-'.$sf_context->getModuleName().' '.
  'action-'.$sf_context->getActionName().' '.
  'meta-event-'.sfConfig::get('pub.meta_event.slug').' '.
  'culture-'.$sf_user->getCulture().' '.
  ''
?>

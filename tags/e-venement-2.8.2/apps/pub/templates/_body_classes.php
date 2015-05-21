<?php echo
  'app-'.$sf_context->getConfiguration()->getApplication().' '.
  'mod-'.$sf_context->getModuleName().' '.
  'action-'.$sf_context->getActionName().' '.
  'meta-event-'.(sfConfig::get('pub.meta_event.slug',false) ? sfConfig::get('pub.meta_event.slug') : 'null').' '.
  'culture-'.$sf_user->getCulture().' '.
  (sfConfig::get('sf_web_debug', false) ? 'env-debug' : '').
  ''
?>

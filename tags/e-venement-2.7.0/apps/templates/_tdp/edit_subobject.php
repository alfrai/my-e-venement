<div class="sf_admin_edit tdp-subobject ui-widget ui-widget-content ui-corner-all tdp-<?php echo strtolower(get_class($sf_data->getRaw('object'))) ?> <?php echo $sf_data->getRaw('object')->isNew() ? 'tdp-object-new' : '' ?>">
  <div class="sf_admin_flashes ui-widget"></div>
  
  <div id="sf_admin_header">
    <?php include_partial($sf_context->getModuleName().'/form_header', array(
      'object' => $object,
      get_class($object) => $object,
      'form' => $form,
      'configuration' => $configuration,
    )) ?>
  </div>
  
  <div id="sf_admin_content">
    <?php include_partial('global/tdp/edit_object', array(
      'object' => $object,
      'form' => new ProfessionalForm($sf_data->getRaw('object')),
      'configuration' => $configuration,
      'helper' => $helper,
      'fields' => sfConfig::get('tdp_config_fields',array()),
      'config' => $config,
    )) ?>
    <?php if ( !$object->isNew() ): ?>
    <div style="display: none;">
      <?php $tmp = new BaseForm(); ?>
      <span style="display: none" class="_delete_csrf_token"><?php echo $tmp->getCSRFToken() ?></span>
      <span style="display: none" class="_delete_confirm"><?php echo __('You are about to remove this function. Are you sure?') ?></span>
    </div>
    <?php endif ?>
  </div>

  <div id="sf_admin_footer">
    <?php //include_partial($sf_context->getModuleName().'/form_footer', array($sf_context->getModuleName() => $object, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

</div>

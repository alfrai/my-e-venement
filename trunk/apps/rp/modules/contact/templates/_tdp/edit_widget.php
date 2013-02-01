<?php use_helper('I18N', 'Date') ?>
<?php include_partial($sf_context->getModuleName().'/assets') ?>

<div id="tdp-content">

<!-- ROOT OBJECT -->
<div class="sf_admin_edit ui-widget tdp-object ui-widget-content ui-corner-all tdp-<?php echo $sf_context->getModuleName() ?>">
<?php include_partial($sf_context->getModuleName().'/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial($sf_context->getModuleName().'/form_header', array(
      'object' => $object,
      get_class($sf_data->getRaw('object')) => $object,
      'form' => $form,
      'configuration' => $configuration,
    )) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('contact/tdp/edit_object', array(
      'object' => $object,
      'form' => $form,
      'configuration' => $configuration,
      'helper' => $helper,
      'fields' => sfConfig::get('tdp_config_fields',array()),
      'config' => $config['object'],
    )) ?>
  </div>

  <div id="sf_admin_footer">
    <?php //include_partial($sf_context->getModuleName().'/form_footer', array($sf_context->getModuleName() => $object, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <?php include_partial($sf_context->getModuleName().'/themeswitcher') ?>
</div>

<!-- SUBOBJECTS -->
<?php if ( isset($config['subobjects']) && !$object->isNew() ): ?>
<?php foreach ( $config['subobjects'] as $link => $subconfig ): ?>
<?php foreach ( $sf_data->getRaw('object')->$link as $subobject ): ?>
  <?php include_partial('contact/tdp/edit_subobject',array(
    'object' => $subobject,
    'form' => new ProfessionalForm($subobject),
    'configuration' => $configuration,
    'helper' => $helper,
    'fields' => sfConfig::get('tdp_config_fields',array()),
    'config' => $subconfig,
  )) ?>
<?php endforeach ?>
<?php
  $subobject = new Professional;
  $obj_class = get_class($sf_data->getRaw('object'));
  $subobject->$obj_class = $sf_data->getRaw('object');
  
  include_partial('contact/tdp/edit_subobject',array(
    'object' => $subobject,
    'form' => new ProfessionalForm($subobject),
    'configuration' => $configuration,
    'helper' => $helper,
    'fields' => sfConfig::get('tdp_config_fields',array()),
    'config' => $subconfig,
  ));
?>
<?php endforeach ?>
<?php endif ?>

</div>

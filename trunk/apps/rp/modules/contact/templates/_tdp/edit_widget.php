<?php use_helper('I18N', 'Date') ?>
<?php include_partial($sf_context->getModuleName().'/assets') ?>
<?php $config = sfConfig::get('tdp_config_edit',array()) ?>

<div id="tdp-content">
<?php include_partial($sf_context->getModuleName().'/flashes') ?>


<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all tdp-<?php echo $sf_context->getModuleName() ?>">

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

<?php if ( isset($config['subobjects']) ): ?>
<?php foreach ( $config['subobjects'] as $link => $subconfig ): ?>
<?php foreach ( $sf_data->getRaw('object')->$link as $subobject ): ?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all tdp-<?php echo strtolower(get_class($subobject)) ?>">

  <div id="sf_admin_header">
    <?php include_partial($sf_context->getModuleName().'/form_header', array(
      'object' => $subobject,
      get_class($subobject) => $subobject,
      'form' => $form,
      'configuration' => $configuration,
    )) ?>
  </div>
  
  <div id="sf_admin_content">
    <?php include_partial('contact/tdp/edit_object', array(
      'object' => $subobject,
      'form' => new ProfessionalForm($subobject),
      'configuration' => $configuration,
      'helper' => $helper,
      'fields' => sfConfig::get('tdp_config_fields',array()),
      'config' => $subconfig,
    )) ?>
  </div>

  <div id="sf_admin_footer">
    <?php //include_partial($sf_context->getModuleName().'/form_footer', array($sf_context->getModuleName() => $object, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <?php include_partial($sf_context->getModuleName().'/themeswitcher') ?>
</div>
<?php endforeach ?>
<?php endforeach ?>
<?php endif ?>

</div>

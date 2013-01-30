<?php
  $ws = $form->getWidgetSchema();
  if ( isset($config['extra_hidden_fields']) && $config['extra_hidden_fields'] )
  foreach ( $config['extra_hidden_fields'] as $field )
    $ws[$field] = new sfWidgetFormInputHidden();
?>
<?php echo form_tag_for($form, '@'.strtolower(get_class($sf_data->getRaw('object')))) ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php include_partial('form_actions',array('form' => $form, 'helper' => $helper, 'contact' => $object,)) ?>

  <?php if ( count($config['title']) ): ?>
  <div id="tdp-widget-header">
    <h1 class="vertical"><?php foreach ( $config['title'] as $field ): ?>
      <?php include_partial($sf_context->getModuleName().'/tdp/edit_field',array(
        'field' => $field,
        'object' => $object,
        'form' => $form,
        'config' => $config,
        'fields' => $fields,
      )) ?>
    <?php endforeach ?></h1>
  </div>
  <?php endif ?>
  
  <?php if ( count($config['lines']) ): ?>
  <div class="tdp-object" id="tdp-object-<?php echo $object->id ?>">
    <?php foreach ( $config['lines'] as $title => $line ): ?>
    <div class="tdp-line <?php echo str_replace(' ','_',strtolower($title)) ?> <?php echo isset($line['extra_class']) ? $line['extra_class'] : '' ?>">
      <?php
        if ( isset($line['show_title']) && $line['show_title'] )
        include_partial($sf_context->getModuleName().'/tdp/edit_line_title',array(
          'line' => $line,
          'title' => $title,
          'config' => $config,
          'object' => $object,
          'fields' => $fields,
        ))
      ?>
      <?php include_partial($sf_context->getModuleName().'/tdp/edit_line',array(
        'line' => $line,
        'object' => $object,
        'form' => $form,
        'line' => $line,
        'config' => $config,
        'fields' => $fields,
      )) ?>
    </div>
    <?php endforeach ?>
  </div>
  <?php endif ?>

</form>

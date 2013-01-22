<?php echo form_tag_for($form, '@'.strtolower(get_class($sf_data->getRaw('object')))) ?>

  <?php if ( count($config['title']) ): ?>
  <div id="tdp-widget-header">
    <h1><?php foreach ( $config['title'] as $field ): ?>
      <?php include_partial($sf_context->getModuleName().'/tdp/edit_field',array(
        'field' => $field,
        'object' => $object,
        'form' => $form,
        'fields' => $fields,
        'config' => $config,
      )) ?>
    <?php endforeach ?></h1>
  </div>
  <?php endif ?>
  
  <?php if ( count($config['lines']) ): ?>
  <div class="tdp-object" id="tdp-object-<?php echo $object->id ?>">
    <?php foreach ( $config['lines'] as $title => $line ): ?>
    <div class="tdp-line <?php echo str_replace(' ','_',strtolower($title)) ?>">
      <?php
        if ( isset($line['show_title']) && $line['show_title'] )
        include_partial($sf_context->getModuleName().'/tdp/edit_line_title',array(
          'line' => $line,
          'title' => $title,
          'config' => $config,
          'object' => $object,
        ))
      ?>
      <?php include_partial($sf_context->getModuleName().'/tdp/edit_line',array(
        'line' => $line,
        'object' => $object,
        'form' => $form,
        'fields' => $fields,
        'config' => $config,
      )) ?>
    </div>
    <?php endforeach ?>
  </div>
  <?php endif ?>

</form>

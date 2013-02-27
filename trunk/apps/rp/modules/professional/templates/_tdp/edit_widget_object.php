<?php echo form_tag_for($form, '@'.$sf_context->getModuleName()) ?>

  <?php if ( is_array($config['title']) ): ?>
  <div id="tdp-widget-header">
    <h1><?php foreach ( $config['title'] as $field ): ?>
      <?php include_partial($sf_context->getModuleName().'/tdp/edit_field',array(
        'field' => $field,
        'form' => $form,
        'fields' => $fields,
        'config' => $config,
      )) ?>
    <?php endforeach ?></h1>
  </div>
  <?php endif ?>
  
  <?php if ( is_array($config['object']) ): ?>
  <div class="tdp-object" id="tdp-object-<?php echo $form->getObject()->id ?>">
    <?php foreach ( $config['object'] as $title => $line ): ?>
    <div class="tdp-line <?php echo str_replace(' ','_',strtolower($title)) ?>">
      <?php
        if ( isset($line['show_title']) && $line['show_title'] )
        include_partial($sf_context->getModuleName().'/tdp/edit_line_title',array(
          'line' => $line,
          'title' => $title,
          'config' => $config,
        ))
      ?>
      <?php include_partial($sf_context->getModuleName().'/tdp/edit_line',array(
        'line' => $line,
        'form' => $form,
        'fields' => $fields,
        'config' => $config,
      )) ?>
    </div>
    <?php endforeach ?>
  </div>
  <?php endif ?>

</form>

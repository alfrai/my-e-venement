<?php if ( !isset($config['credentials'])
        || isset($config['credentials']) && $config['credentials'] && $sf_user->hasCredential($config['credentials']) ): ?>
<?php
  $obj_class = get_class($sf_data->getRaw('object'));
  $ws = $form->getWidgetSchema();
  if ( isset($config['extra_hidden_fields']) && $config['extra_hidden_fields'] )
  foreach ( $config['extra_hidden_fields'] as $field )
  if ( !$object->isNew() || !in_array($field, $config->getRaw('new_title')) )
  {
    $ws[$field] = new sfWidgetFormInputHidden(array(),array(
      'id' => '',
    ));
  }
?>
<?php echo form_tag_for($form, '@'.strtolower($obj_class)) ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php include_partial('form_actions',array('form' => $form, 'helper' => $helper, 'contact' => $object,)) ?>

  <?php if ( count($config['title']) ): ?>
  <div class="tdp-widget-header ui-widget-header ui-corner-all">
    <h1 class="vertical"><?php foreach ( $config[$object->isNew() ? 'new_title' : 'title'] as $field ): ?>
      <?php include_partial('global/tdp/edit_field',array(
        'field' => $field,
        'object' => $object,
        'form' => $form,
        'config' => $config,
        'fields' => $fields,
      )) ?>
    <?php endforeach ?></h1>
    <?php if ( !$object->isNew() ): ?>
    <div class="tdp-actions">
      <a class="tdp-delete"
         href="<?php echo url_for(strtolower($obj_class).'/delete?id='.$object->id) ?>"
      >Supprimer</a>
    </div>
    <?php endif ?>
  </div>
  <?php endif ?>
  
  <?php if ( count($config['lines']) ): ?>
  <div class="tdp-object" id="tdp-object-<?php echo $obj_class.'-'.$object->id ?>">
    <?php foreach ( $config['lines'] as $title => $line ): ?>
    <div class="tdp-line <?php echo str_replace(' ','_',strtolower($title)) ?> <?php echo isset($line['extra_class']) ? $line['extra_class'] : '' ?>">
      <?php
        if ( isset($line['show_title']) && $line['show_title'] )
        include_partial('global/tdp/edit_line_title',array(
          'line' => $line,
          'title' => $title,
          'config' => $config,
          'object' => $object,
          'fields' => $fields,
        ))
      ?>
      <?php include_partial('global/tdp/edit_line',array(
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
<?php endif ?>

<?php if ( substr($field,0,1) == "_" ): ?>
  <?php
    try {
      include_partial($sf_context->getModuleName().'/tdp/'.substr($field,1),array(
        'form' => $form,
        $sf_context->getModuleName() => $object,
        'object' => $object,
        'show_labels' => isset($show_labels) && $show_labels,
      ));
    }
    catch ( sfRenderException $e )
    {
      include_partial(substr($field,1),array(
        'form' => $form,
        $sf_context->getModuleName() => $form->getObject(),
      ));
    }
  ?>
<?php else: ?>
<span title="<?php if ( !(isset($show_messages) && !$show_messages) ) echo __(isset($fields[$field]) && isset($fields[$field]['label']) ? $fields[$field]['label'] : ucfirst(str_replace('_',' ',$field))) ?>" class="tdp-<?php echo $field ?>">
  <?php if ( isset($form[$field]) ): ?>
    <?php if ( isset($show_labels) && $show_labels ): ?>
      <?php if ( isset($fields[$field]) ) $form[$field]->getWidget()->setLabel($fields[$field]['label']) ?>
      <?php echo $form[$field]->renderLabel() ?>
    <?php endif ?>
    
    <?php echo $form[$field] ?>
  <?php elseif ( $object->hasColumn($field) && is_object($object->getRaw($field)) ): ?>
    <?php echo $object->$field ?>
  <?php elseif ( $object->get($field) ): ?>
    <?php echo $object->get($field) ?>
  <?php elseif ( $field == 'this' ): ?>
    <?php echo $object ?>
  <?php else: ?>
    <?php echo __($field) ?>
  <?php endif ?>
</span>
<?php endif ?>

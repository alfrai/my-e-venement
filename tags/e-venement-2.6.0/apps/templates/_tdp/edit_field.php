<?php if ( substr($field,0,1) == "_" ): ?>
  <?php
    try {
      include_partial($sf_context->getModuleName().'/tdp/'.substr($field,1),array(
        'form' => $form,
        $sf_context->getModuleName() => $object,
        'object' => $object,
        'show_labels' => isset($show_labels) && $show_labels,
        'field' => substr($field,1),
        'fields' => $fields,
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
<?php
  $class = array();
  $class[] = 'tdp-'.$field;
  $class[] = 'sf_admin_form_field_'.$field;
  
  if ( isset($form[$field]) && $form[$field]->hasError() )
    $class[] = 'ui-state-error ui-corner-all';
?>
<span title="<?php if ( !(isset($show_messages) && !$show_messages) ) echo __(isset($fields[$field]) && isset($fields[$field]['label']) ? $fields[$field]['label'] : ucfirst(str_replace('_',' ',$field))) ?>" class="<?php echo implode(' ',$class) ?>">
  <?php if ( isset($form[$field]) ): ?>
    <?php if ( isset($show_labels) && $show_labels ): ?>
      <?php if ( isset($fields[$field]) ) $form[$field]->getWidget()->setLabel($fields[$field]['label']) ?>
      <?php echo $form[$field]->renderLabel() ?>
    <?php endif ?>
    
    <?php echo $form[$field] ?>
    <?php if ( $form[$field]->hasError() ): ?>
      <div class="errors">
        <span class="ui-icon ui-icon-alert floatleft"></span>
        <?php echo $form[$field]->renderError() ?>
      </div>
    <?php endif ?>
  <?php elseif ( $object->hasColumn($field) ): ?>
    <?php echo $object->$field ?>
  <?php elseif ( $object->hasRelation($field) ): ?>
    <?php echo link_to($object->get($field), strtolower($field).'/edit?id='.$object->get($field)->id) ?>
  <?php elseif ( $field == 'this' ): ?>
    <?php echo $object ?>
  <?php else: ?>
    <?php //echo __($field) ?>
  <?php endif ?>
</span>
<?php endif ?>

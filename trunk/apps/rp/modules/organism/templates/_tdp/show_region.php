<span title="<?php echo __(isset($fields[$field]) && isset($fields[$field]['label']) ? $fields[$field]['label'] : ucfirst($field)) ?>" class="<?php echo $field ?>">
  <?php if ( isset($show_labels) && $show_labels ): ?>
    <?php if ( isset($fields[$field]) ) $form[$field]->getWidget()->setLabel($fields[$field]['label']) ?>
    <?php echo $form[$field]->renderLabel() ?>
  <?php endif ?>
  
  <?php echo ($region = $object->getRegion()) ? $region : '&nbsp;' ?>
</span>

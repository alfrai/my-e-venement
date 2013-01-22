<?php foreach ( $line['fields'] as $field ): ?>
  <?php include_partial($sf_context->getModuleName().'/tdp/edit_field',array(
    'show_labels' => isset($line['show_labels']) && $line['show_labels'],
    'show_messages' => !(isset($line['show_messages']) && !$line['show_messages']),
    'field' => $field,
    'object' => $object,
    'form' => $form,
    'fields' => $fields,
    'config' => $config,
  )) ?>
<?php endforeach ?>

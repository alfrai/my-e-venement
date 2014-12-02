<?php if ( !(isset($line['credential']) && !$sf_user->hasCredential($line['credential'])) ): ?>
<?php foreach ( $line['fields'] as $field ): ?>
  <?php include_partial('global/tdp/edit_field',array(
    'show_labels' => isset($line['show_labels']) && $line['show_labels'],
    'show_messages' => !(isset($line['show_messages']) && !$line['show_messages']),
    'field' => $field,
    'object' => $object,
    'form' => $form,
    'line' => $line,
    'config' => $config,
    'fields' => $fields,
  )) ?>
<?php endforeach ?>
<?php endif ?>

<?php include_partial(
  'form_links_field',
  array(
    'name'  => 'exceptions_to_add',
    'label' => __('Exceptions to add'),
    'size'  => '70',
    'with_submit' => true,
    'helper' => __('Format: %%format%%', array('%%format%%' => 'A1--A3[, A2--A4[, ...]]')),
  )
) ?>

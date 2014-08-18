<?php include_partial(
  'form_ranks_field',
  array(
    'name'  => 'contiguous',
    'label' => __('Contiguous?'),
    'type'  => 'checkbox',
    'value' => 'yes',
    'size'  => false,
    'attributes' => array('checked' => 'checked'),
  )
) ?>
<?php include_partial(
  'form_ranks_field',
  array(
    'name'  => 'format',
    'label' => __('Format'),
    'value' => '%row%%num%',
    'size'  => 20,
    'class' => 'align-left',
  )
) ?>

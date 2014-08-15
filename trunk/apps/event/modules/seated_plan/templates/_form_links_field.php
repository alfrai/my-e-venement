<?php $params = array(
  'name'      => NULL,
  'label'     => NULL,
  'button'    => NULL,
  'type'      => NULL,
  'value'     => NULL,
  'size'      => NULL,
  'class'     => NULL,
  'helper'    => NULL,
  'attributes' => NULL,
  'with_submit' => NULL,
  'submit_label' => NULL,
) ?>
<?php
  foreach ( $params as $param => $dummy )
  {
    if ( isset(${$param}) )
      $params[$param] = $$param;
    else
      unset($params[$param]);
  }
  
  $params['batch'] = 'links';
  include_partial('form_batch_field', $params);
?>

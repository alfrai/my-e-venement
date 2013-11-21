<?php
  $name = 'type_of_resources_id';
  include_partial('form_field_with_credential',array(
    'name'  => $name = 'type_of_resources_id',
    'label' => 'Type of resources',
    'help'  => '',
    'class' => 'sf_admin_form_row sf_admin_foreignkey sf_admin_form_field_'.$name,
    'credential' => 'pr-social-resources',
    'attributes' => array(),
    'form'  => $form,
  ));
?>

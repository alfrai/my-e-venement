<?php
  $name = 'familial_situation_id';
  include_partial('form_field_with_credential',array(
    'name'  => $name,
    'label' => 'Familial situation',
    'help'  => '',
    'class' => 'sf_admin_form_row sf_admin_foreignkey sf_admin_form_field_'.$name,
    'credential' => 'pr-social-situation',
    'attributes' => array(),
    'form'  => $form,
  ));
?>

<?php
  $name = 'familial_quotient_id';
  include_partial('form_field_with_credential',array(
    'name'  => $name,
    'label' => 'Familial quotient',
    'help'  => '',
    'class' => 'sf_admin_form_row sf_admin_foreignkey sf_admin_form_field_'.$name,
    'credential' => 'pr-social-quotient',
    'attributes' => array(),
    'form'  => $form,
  ));
?>

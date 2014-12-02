<?php
  $str = array(
    'title' => __('Contacts list'),
    'nb' => __('%%nb%% contacts',array('%%nb%%' => $form->getObject()->Contacts->count())),
    'collection' => 'Contacts',
  );
  include_partial('form_objects_list',array(
    'form' => $form,
    'str' => $str,
  ));
?>

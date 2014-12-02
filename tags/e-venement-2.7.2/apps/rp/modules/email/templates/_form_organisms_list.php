<?php
  $str = array(
    'title' => __('Organisms list'),
    'nb' => __('%%nb%% organisms',array('%%nb%%' => $form->getObject()->Organisms->count())),
    'collection' => 'Organisms',
  );
  include_partial('form_objects_list',array(
    'form' => $form,
    'str' => $str,
  ));
?>

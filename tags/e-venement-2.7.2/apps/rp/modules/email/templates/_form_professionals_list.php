<?php
  $str = array(
    'title' => __('Professionals list'),
    'nb' => __('%%nb%% professionals',array('%%nb%%' => $form->getObject()->Professionals->count())),
    'collection' => 'Professionals',
  );
  include_partial('form_objects_list',array(
    'form' => $form,
    'str' => $str,
  ));
?>

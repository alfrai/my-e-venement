<?php
  if ( isset($object) )
    include_partial($sf_context->getModuleName().'/tdp/side_widget_object',array(
      'object' => $object,
      'config' => $config,
    ));
  else
    include_partial($sf_context->getModuleName().'/tdp/side_widget_list',array('filters' => $filters));


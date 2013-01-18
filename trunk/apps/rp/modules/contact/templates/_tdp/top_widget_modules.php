<?php
  echo link_to('<span>'.__('Contacts').'</span>','@contact',array('class' => 'contact choice '.($sf_context->getModuleName() == 'contact' ? 'current' : 'other')));
  echo link_to('<span>'.__('Organisms').'</span>','@organism',array('class' => 'organism choice '.($sf_context->getModuleName() == 'organism' ? 'current' : 'other')));


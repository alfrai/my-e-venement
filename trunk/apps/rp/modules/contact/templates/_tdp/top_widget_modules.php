<?php
  if ( $sf_user->hasCredential('pr-contact') )
    echo link_to('<span>'.__('Contacts').'</span>','@contact',array('class' => 'contact choice '.($sf_context->getModuleName() == 'contact' ? 'current' : 'other')));
  else
    echo '<a href="#" class="contact choice other">'.__('Contacts').'</a>';
  
  if ( $sf_user->hasCredential('pr-organism') )
    echo link_to('<span>'.__('Organisms').'</span>','@organism',array('class' => 'organism choice '.($sf_context->getModuleName() == 'organism' ? 'current' : 'other')));
  else
    echo '<a href="#" class="organism choice other">'.__('Organisms').'</a>';

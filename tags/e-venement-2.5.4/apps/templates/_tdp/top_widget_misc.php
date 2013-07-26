<?php
  // member cards
  if ( sfConfig::get('app_cards_enable') )
  if ( $sf_user->hasCredential('pr-card-view') )
  echo link_to(__('Cards'),isset($object) && $sf_context->getModuleName() == 'contact' ? 'contact/card?id='.$object->id : 'member_card/check',array(
    'title' => isset($object) && $sf_context->getModuleName() == 'contact'
      ? __("%%c%%'s member cards",array('%%c%%' => $object))
      : __('Member card check',null,'menu'),
  ));
  
  $active_filters = false;
  if ( !isset($object) )
  foreach ( $hasFilters as $key => $value )
  if ( $value )
  {
    $active_filters = true;
    break;
  }
  
  // groups
  if ( $sf_user->hasCredential('pr-group') )
  {
    echo link_to(__('Groups'), $active_filters ? 'contact/group' : '@group',array(
      'title' => $active_filters ? __('Export to group') : __('Group List'),
    ));
  }
  
  // emailing
  if ( $sf_user->hasCredential('pr-emailing') )
  {
    if ( $active_filters )
      echo link_to(__('Emailing'),$sf_context->getModuleName().'/emailing',array('title' => __('Create emailing')));
    else
      echo '<a href="#">'.__('Emailing').'</a>';
  }
?>

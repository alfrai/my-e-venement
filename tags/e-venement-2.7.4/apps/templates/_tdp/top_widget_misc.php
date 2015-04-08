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
    echo link_to(__('Groups'), $active_filters ? $sf_context->getModuleName().'/group' : '@group',array(
      'title' => $active_filters ? __('Export to group') : __('Group List'),
    ));
  }
  
  // emailing
  if ( $active_filters )
  {
    if ( $sf_user->hasCredential('pr-emailing') )
      echo link_to(__('Emailing'), $sf_context->getModuleName().'/emailing',array('title' => __('Create emailing')));
  }
  elseif ( is_object($object) )
  {
    if ( $sf_user->hasCredential('pr-contact-csv') )
      echo link_to(__('vCard'), $sf_context->getModuleName().'/vcf?id='.$object->id, array('target' => '_blank'));
  }
?>

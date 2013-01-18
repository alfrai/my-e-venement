<?php
  $js_add_ids_to_url = "javascript: form = $('#tdp-content').clone(true); form.find('[name=batch_action]').remove(); $(this).attr('href',$(this).attr('href')+'&'+form.serialize());";
  
  echo link_to(__('New',null,'sf_admin'),$sf_context->getModuleName().'/new',array('class' => 'action'));
  echo link_to(__('Delete',null,'sf_admin'), $sf_context->getModuleName().( isset($object) ? '/delete?id='.$object->id : '/batch?batch_action=batchDelete'), array(
    'class' => 'action',
    'onclick' => isset($object)
      ? "javascript: $('.sf_admin_form .sf_admin_action_delete a').click(); return false;"
      : $js_add_ids_to_url,
  ));
  
  if ( !isset($object) )
  {
    $merge = method_exists($sf_data->getRaw('sf_context')->getActionStack()->getFirstEntry()->getActionInstance(),'executeBatchMerge');
    echo $merge ? link_to(__('Merge'),$sf_context->getModuleName().'/batch?batch_action=batchMerge', array('class' => 'action', 'onclick' => $js_add_ids_to_url)) : '<a href="#">'.__('Merge').'</a>';
  }
  else
  {
    echo link_to(__('Update',null,'sf_admin'),$sf_context->getModuleName().'/update?id='.$object->id,array(
      'onclick' => "javascript: $('.sf_admin_form .sf_admin_action_save a').click(); return false;",
    ));
  }
?>

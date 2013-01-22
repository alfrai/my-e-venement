<?php
  $js_add_ids_to_url = "javascript: form = $('#tdp-content').clone(true); form.find('[name=batch_action]').remove(); $(this).attr('href',$(this).attr('href')+'&'+form.serialize());";
  
  echo $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-new')
    ? link_to(__('New',null,'sf_admin'),$sf_context->getModuleName().'/new',array('class' => 'action'))
    : '<a href="#">'.__('New',null,'sf_admin').'</a>';
  echo $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-del')
    ? link_to(__('Delete',null,'sf_admin'), $sf_context->getModuleName().( isset($object) ? '/delete?id='.$object->id : '/batch?batch_action=batchDelete'), array(
      'class' => 'action',
      'onclick' => isset($object)
        ? "javascript: $('.sf_admin_form .sf_admin_action_delete a').click(); return false;"
        : $js_add_ids_to_url,
    ))
    : '<a href="#">'.__('Delete',null,'sf_admin').'</a>';
?>
<?php if ( !isset($object) ): ?>
<div class="tdp-submenu">
  <?php
    if ( $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-edit') && $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-del') )
    {
      $merge = method_exists($sf_data->getRaw('sf_context')->getActionStack()->getFirstEntry()->getActionInstance(),'executeBatchMerge');
      echo $merge ? link_to(__('Merge'),$sf_context->getModuleName().'/batch?batch_action=batchMerge', array('class' => 'group', 'onclick' => $js_add_ids_to_url)) : '<a class="group" href="#">'.__('Merge').'</a>';
    }
    
    echo link_to(__('Duplicates'),$sf_context->getModuleName().'/duplicates',array('class' => 'group'));
    echo $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-edit') ? link_to(__('Labels'),$sf_context->getModuleName().'/labels',array('class' => 'group')) : '<a class="group" href="#">'.__('Labels').'</a>';
    echo link_to(__('Geolocalize'),$sf_context->getModuleName().'/map',array('class' => 'group'));
  ?>
</div><?php echo $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-csv') ? link_to(__('Extract'),$sf_context->getModuleName().'/csv',array('class' => 'group')) : '<a class="group" href="#">'.__('Extract').'</a>' ?>

<?php else: ?>
<?php if ( $sf_user->hasCredential('pr-'.$sf_context->getModuleName().'-edit') ): ?>
<?php echo link_to(__('Update',null,'sf_admin'),$sf_context->getModuleName().'/update?id='.$object->id,array(
          'onclick' => "javascript: $('.sf_admin_form .sf_admin_action_save a').click(); return false;",
        )) ?>
<?php else: ?>
<a href="#"><?php echo __('Update',null,'sf_admin') ?></a>
<?php endif ?>

<?php endif ?>

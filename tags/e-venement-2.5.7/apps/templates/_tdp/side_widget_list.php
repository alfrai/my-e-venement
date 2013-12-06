<div id="tdp-side-bar" class="tdp-container list ui-widget-content ui-corner-all">
<?php include_partial('global/list_header') ?>
<form class="tdp-container filters" action="<?php echo url_for($sf_context->getModuleName().'_collection', array('action' => 'filter')) ?>" method="post">
  <?php echo $filters->renderHiddenFields() ?>
  <div class="tdp-side-widget" id="tdp-side-categories">
    <h2 class="ui-widget-header ui-corner-all"><?php echo __('Categories') ?> <?php if ( $sf_user->hasCredential('admin-org') ) echo link_to('+','organism_category/new') ?></h2>
    <?php $filters['organism_category_id']->getWidget()->setOption('expanded',true) ?>
    <?php echo $filters['organism_category_id']; ?>
  </div>
  <div class="tdp-side-widget" id="tdp-side-groups">
    <h2 class="ui-widget-header ui-corner-all"><?php echo __('Groups') ?> <?php if ( $sf_user->hasCredential('pr-group-perso') || $sf_user->hasCredential('pr-group-common') ) echo link_to('+','group/new') ?></h2>
    <?php $filters['groups_list']->getWidget()->setOption('expanded',true) ?>
    <?php echo $filters['groups_list']; ?>
    
    <a href="<?php echo url_for($sf_context->getModuleName().'/batchAddToGroup') ?>" class="batch-add-to group"></a>
  </div>
</form>
</div>

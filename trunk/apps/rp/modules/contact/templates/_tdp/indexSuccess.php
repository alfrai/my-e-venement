<?php include_partial('contact/tdp/assets') ?>

<?php include_partial('contact/tdp/list_top_widget',array('filters' => $filters, 'configuration' => $configuration,)) ?>
<?php include_partial('contact/tdp/list_side_widget',array('filters' => $filters)) ?>
<?php include_partial('contact/tdp/list_list_widget',array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'hasFilters' => $hasFilters)) ?>
<div class="clear"></div>

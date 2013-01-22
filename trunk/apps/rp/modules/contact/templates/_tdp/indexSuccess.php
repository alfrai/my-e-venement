<?php include_partial('contact/tdp/assets') ?>

<?php include_partial('contact/tdp/top_widget',array('filters' => $filters, 'hasFilters' => $hasFilters, 'configuration' => $configuration, 'object' => NULL,)) ?>
<?php include_partial('contact/tdp/side_widget',array('filters' => $filters, 'object' => isset($object) ? $object : NULL)) ?>
<?php include_partial('contact/tdp/list_widget',array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'hasFilters' => $hasFilters)) ?>
<div class="clear"></div>

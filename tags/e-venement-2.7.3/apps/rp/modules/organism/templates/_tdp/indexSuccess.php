<?php include_partial('contact/tdp/assets') ?>

<?php include_partial('global/tdp/top_widget',array('filters' => $filters, 'hasFilters' => $hasFilters, 'configuration' => $configuration, 'object' => NULL, 'config' => sfConfig::get('tdp_config_list'),)) ?>
<?php include_partial('global/tdp/side_widget',array('filters' => $filters, 'object' => isset($object) ? $object : NULL)) ?>
<?php include_partial('global/tdp/list_widget',array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'hasFilters' => $hasFilters)) ?>
<div class="clear"></div>

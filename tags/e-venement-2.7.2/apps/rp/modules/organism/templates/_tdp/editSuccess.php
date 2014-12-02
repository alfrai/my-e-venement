<?php include_partial('contact/tdp/assets') ?>

<?php $config = sfConfig::get('tdp_config_edit') ?>
<?php include_partial('global/tdp/top_widget',array('filters' => $filters, 'hasFilters' => $hasFilters, 'configuration' => $configuration, 'object' => $organism, 'config' => $config['object'],)) ?>
<?php include_partial('global/tdp/side_widget',array('object' => $organism, 'config' => $config,)) ?>
<?php include_partial('global/tdp/edit_widget',array('organism' => $organism, 'object' => $organism, 'helper' => $helper, 'hasFilters' => $hasFilters, 'form' => $form, 'configuration' => $configuration, 'config' => $config,)) ?>
<div class="clear"></div>

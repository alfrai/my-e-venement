<?php include_partial('contact/tdp/assets') ?>

<?php include_partial('contact/tdp/top_widget',array('filters' => $filters, 'hasFilters' => $hasFilters, 'configuration' => $configuration,'object' => $contact)) ?>
<?php include_partial('contact/tdp/side_widget',array('object' => $contact)) ?>
<?php include_partial('contact/tdp/edit_widget',array('contact' => $contact, 'object' => $contact, 'helper' => $helper, 'hasFilters' => $hasFilters, 'form' => $form, 'configuration' => $configuration,)) ?>
<div class="clear"></div>

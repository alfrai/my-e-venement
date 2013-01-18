    <a
    href="#sf_admin_filter" id="sf_admin_filter_button" onclick="javascript: $('#tdp-filters').toggle();"><?php echo __('Filters',null,'sf_admin') ?></a><a
    href="#update-filters" onclick="javascript: $('#tdp-side-bar').submit();" id="tdp-update-filters" title="<?php echo __('Update filters') ?>"><?php echo __('Update',null,'sf_admin') ?></a><?php
    echo link_to(__('Reset', array(), 'sf_admin'), 'contact_collection', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'id' => 'tdp-reset-filters')) ?>

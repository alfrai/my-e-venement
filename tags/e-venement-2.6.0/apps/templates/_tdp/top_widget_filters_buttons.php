    <a
    href="#<?php echo isset($object) ? '' : 'sf_admin_filter' ?>" id="sf_admin_filter_button" onclick="javascript: $('#tdp-filters').toggle();"><?php echo __('Filters',null,'sf_admin') ?></a><a
    href="#<?php echo isset($object) ? '' : 'update-filters' ?>" onclick="javascript: $('#tdp-side-bar .filters').submit(); return false;" id="tdp-update-filters" title="<?php echo __('Update filters') ?>"><?php echo __('Update',null,'sf_admin') ?></a><?php
    echo isset($object) ? '<a href="#">'.__('Reset',null,'sf_admin').'</a>' : link_to(__('Reset', array(), 'sf_admin'), $sf_context->getModuleName().'_collection', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'id' => 'tdp-reset-filters')) ?>

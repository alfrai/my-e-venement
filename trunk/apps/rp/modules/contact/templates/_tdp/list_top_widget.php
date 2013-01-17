<div id="tdp-top-bar" class="tdp-container actions">
  <div id="tdp-top-bar-type" class="tdp-top-widget skew"><?php
    echo link_to('<span>'.__('Contacts').'</span>','@contact',array('class' => 'contact choice '.($sf_context->getModuleName() == 'contact' ? 'current' : 'other')));
    echo link_to('<span>'.__('Organisms').'</span>','@organism',array('class' => 'organism choice'.($sf_context->getModuleName() == 'organism' ? 'current' : 'other')));
  ?></div>
  <div id="tdp-top-bar-actions" class="tdp-top-widget vertical">
    <?php echo link_to(__('New',null,'sf_admin'),$sf_context->getModuleName().'/new',array('class' => 'action'))
    ?><a <?php echo isset($object) ? 'href="'.url_for($sf_context->getModuleName().'/delete?id='.$object->id).'"' : '' ?>><?php echo __('Delete',null,'sf_admin') ?></a>
  </div>
  <div id="tdp-top-bar-filters" class="tdp-top-widget vertical">
    <a
    href="#sf_admin_filter" id="sf_admin_filter_button" onclick="javascript: $('#tdp-filters').toggle();"><?php echo __('Filters',null,'sf_admin') ?></a><a
    href="#update-filters" onclick="javascript: $('#tdp-side-bar').submit();" id="tdp-update-filters" title="<?php echo __('Update filters') ?>"><?php echo __('Update',null,'sf_admin') ?></a><?php
    echo link_to(__('Reset', array(), 'sf_admin'), 'contact_collection', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'id' => 'tdp-reset-filters')) ?>
  </div>
  <div id="sf_admin_bar" style="display: none;">
    <?php include_partial('contact/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>
</div>

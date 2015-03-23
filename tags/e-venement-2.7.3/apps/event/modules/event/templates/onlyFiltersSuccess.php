<div><!-- to avoid a jQuery f***ing "bug" -->

<div id="sf_admin_filters_buttons" class="fg-buttonset fg-buttonset-multi ui-state-default">
  <a href="#sf_admin_filter" id="sf_admin_filter_button" class="fg-button ui-state-default fg-button-icon-left ui-corner-left"><?php echo UIHelper::addIconByConf('filters') . __('Filters', array(), 'sf_admin') ?></a>
  <?php echo link_to(UIHelper::addIconByConf('reset') . __('Reset', array(), 'sf_admin'), 'event_collection', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'class' => 'fg-button ui-state-default fg-button-icon-left ui-corner-right')) ?></span>
</div>
<?php include_partial('filters', array(
  'form' => $filters,
  'configuration' => $configuration,
)) ?>

</div>

      <div id="sf_admin_filters_buttons" class="fg-buttonset fg-buttonset-multi ui-state-default">
        <a href="#sf_admin_filter" id="sf_admin_filter_button" class="fg-button ui-state-default fg-button-icon-left ui-corner-left"><?php echo UIHelper::addIconByConf('filters') . __('Filters', array(), 'sf_admin') ?></a>
        <?php echo link_to(UIHelper::addIconByConf('reset') . __('Reset', array(), 'sf_admin'), 'contact_collection', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'class' => 'fg-button ui-state-default fg-button-icon-left ui-corner-right')) ?></span>
      </div>
      <h1 class="contact"><a href="<?php echo $module == 'contact' ? '#' : url_for('@contact') ?>"><?php echo __('Contact List', array(), 'messages') ?></a></h1>
      <h1 class="organism"><a href="<?php echo $module == 'organism' ? '#' : url_for('@organism') ?>"><?php echo __('Organisms list', array(), 'messages') ?></a></h1>

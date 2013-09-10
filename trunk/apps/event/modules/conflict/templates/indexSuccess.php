<div id="sf_admin_container">
  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('list_header', array()) ?>
  </div>

      <div id="sf_admin_bar ui-helper-hidden" style="display:none">
      <?php include_partial('filters', array()) ?>
    </div>
  
  <div id="sf_admin_content">
      <form action="<?php echo url_for('conflict/index', array('action' => 'batch')) ?>" method="post" id="sf_admin_content_form">
    
      <!--
      <div class="sf_admin_actions_block floatleft">
      	<a tabindex="0" href="#sf_admin_actions_menu" class="fg-button fg-button-icon-right ui-widget ui-state-default ui-corner-all" id="sf_admin_actions_button">
      	  <span class="ui-icon ui-icon-triangle-1-s"></span>
      	  <?php echo __('Actions') ?>
      	</a>
      	<div id="sf_admin_actions_menu" class="ui-helper-hidden fg-menu fg-menu-has-icons">
      		<ul class="sf_admin_actions" id="sf_admin_actions_menu_list">
      			<?php include_partial('list_actions', array()) ?>
      		</ul>
      	</div>
      </div>
      -->

      <?php include_partial('list', array('manifestations' => $manifestations, 'conflicts' => $conflicts)) ?>

      <ul class="sf_admin_actions">
        <?php include_partial('list_batch_actions', array()) ?>
      </ul>

          </form>
      </div>

  <div id="sf_admin_footer">
    <?php include_partial('list_footer', array()) ?>
  </div>

  <?php include_partial('themeswitcher') ?>
</div>

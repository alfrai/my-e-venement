<?php use_helper('I18N') ?>
<?php include_partial('sfGuardAuth/assets') ?>

<div class="about-home">
  <?php echo get_partial('global/about', array('form' => $form)) ?>
</div>

<div id="sf_admin_container">
  &nbsp;
  <div id="sf_admin_content">
  
  <div class="ui-grid-table ui-widget ui-helper-reset ui-helper-clearfix">
    <div class="ui-widget-content ui-corner-all login">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Signin', null, 'sf_guard') ?></h2>
      </div>
      <?php echo get_partial('sfGuardAuth/signin_form', array('form' => $form)) ?>
    </div>
    
    <div class="ui-widget-content ui-corner-all">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Libre Informatique', null, 'sf_guard') ?></h2>
      </div>
      <?php include_partial('global/libre-informatique') ?>
    </div>
    
    
  </div>
  </div>
</div>

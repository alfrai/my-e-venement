<?php use_helper('I18N') ?>
<?php include_partial('sfGuardAuth/assets') ?>

<?php echo get_partial('global/flashes') ?>

<div class="about-home">
  <?php echo get_partial('global/about') ?>
</div>

<div id="sf_admin_container">
  &nbsp;
  <div id="sf_admin_content">
  
  <div class="ui-grid-table ui-widget ui-helper-reset ui-helper-clearfix">
    <div class="ui-widget-content ui-corner-all login">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __("You don't have access to this part.") ?></h2>
      </div>
      <div class="error ui-state-error ui-corner-all">
        <span class="ui-icon ui-icon-alert floatleft"></span>&nbsp;
        <?php echo __('Oops! The screen you asked for is secure and you do not have proper credentials.') ?>
      </div>
      <p><?php echo sfContext::getInstance()->getRequest()->getUri() ?></p>
      <h3><?php echo __('Login below to gain access') ?></h3>
      <?php echo get_component('sfGuardAuth', 'signin_form') ?>
    </div>
    
    <div class="ui-widget-content forgot-pwd ui-corner-all">
      <?php $forgot_form = new sfGuardRequestForgotPasswordForm() ?>
      <?php include_partial('sfGuardForgotPassword/index', array('form' => $forgot_form)) ?>
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

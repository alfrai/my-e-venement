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
      <?php if ( true ): ?>
        <div id="ipv6">
          <p class="on">
            <?php if ( $ipv6['on'] ): ?>
            <?php echo image_tag('button-ipv6-on.png') ?>
            <?php endif ?>
          </p>
          <?php if ( $ipv6['ready'] ): ?>
          <p class="ready">
            <?php echo image_tag('button-ipv6-ready.png') ?>
          </p>
          <?php endif ?>
        </div>
      <?php endif ?>
    </div>
    
    <?php if ( sfConfig::get('project_demo') ): ?>
    <div class="ui-widget-content ui-corner-all">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Demonstration', null, 'sf_guard') ?></h2>
      </div>
      <?php include_partial('global/demo') ?>
    </div>
    
    <?php else: ?>
    
    <?php /*
    <div class="ui-widget-content forgot-pwd ui-corner-all">
      <?php $forgot_form = new sfGuardRequestForgotPasswordForm() ?>
      <?php include_partial('sfGuardForgotPassword/index', array('form' => $forgot_form)) ?>
    </div>
    */ ?>
    
    <?php endif; ?>
    
    <div class="ui-widget-content ui-corner-all" id="company">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Libre Informatique', null, 'sf_guard') ?></h2>
      </div>
      <?php include_partial('global/libre-informatique') ?>
    </div>
    
    
  </div>
  </div>
</div>

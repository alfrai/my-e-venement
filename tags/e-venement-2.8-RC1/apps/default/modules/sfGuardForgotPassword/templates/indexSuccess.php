<?php include_partial('sfGuardAuth/assets') ?>
<?php include_partial('global/flashes') ?>

<div class="about-home">
  <?php include_partial('global/about') ?>
</div>

<div id="sf_admin_container">
  &nbsp;
  <div id="sf_admin_content">
    <div class="ui-grid-table ui-widget ui-helper-reset ui-helper-clearfix">
      <div class="ui-widget-content ui-corner-all forgot-pwd">
      <?php include_partial('sfGuardForgotPassword/index',array('form' => $form)) ?>
      </div>
    </div>
  </div>
</div>

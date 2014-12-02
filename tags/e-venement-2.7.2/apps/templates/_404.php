<?php use_javascript('jquery','first') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/reset.css','first') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/jquery/redmond/jquery-ui.custom.css') ?>
<?php use_stylesheet('404.css') ?>
<div id="404" class="ui-widget ui-widget-content ui-corner-all">
  <h1 class="ui-widget-header ui-corner-all"><?php echo __('Object not found',null,'menu') ?></h1>
  <div class="ui-widget-content ui-corner-all">
    <p><?php echo __('The requested object does not exist',null,'menu') ?></p>
    <p><?php echo __('You can <a href="javascript: window.history.back();">go back</a> from where you came and try again...',null,'menu') ?></p>
  </div>
</div>

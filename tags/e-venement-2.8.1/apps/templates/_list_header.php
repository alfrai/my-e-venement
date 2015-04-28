<?php use_javascript('integrated-search') ?>
<form class="fg-button ui-widget ui-corner-all ui-state-default" action="<?php echo url_for(sfContext::getInstance()->getModuleName().'/search') ?>" method="get" id="list-integrated-search" target="_blank">
  <input type="hidden" name="url" value="<?php echo url_for('@'.sfContext::getInstance()->getModuleName()) ?>" />
  <label><?php echo __("Search") ?>:</label>
  <input type="text" name="s" value="<?php echo sfContext::getInstance()->getRequest()->getParameter('s') ?>" />
</form>

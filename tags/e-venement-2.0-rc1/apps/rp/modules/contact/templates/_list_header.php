<form class="fg-button ui-widget ui-corner-all ui-state-default" action="<?php echo url_for('contact/search') ?>" method="get" id="list-integrated-search">
  <input type="hidden" name="contact_url" value="<?php echo url_for('@contact') ?>" />
  <label><?php echo __("Search") ?>:</label>
  <input type="text" name="s" value="<?php echo sfContext::getInstance()->getRequest()->getParameter('s') ?>" />
</form>

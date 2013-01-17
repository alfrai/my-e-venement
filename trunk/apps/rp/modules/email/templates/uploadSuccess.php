<?php include_partial('assets') ?>
<form action="<?php echo url_for('email/attach') ?>" method="post" enctype="multipart/form-data" id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Upload attachment') ?></h1>
  </div>
  <div class="ui-corner-all ui-widget-content" id="upload-content">
    <p><input type="file" name="attachment" /></p>
    <p><input type="submit" name="" value="<?php echo __('Update',null,'sf_admin') ?>" /><input type="hidden" name="email[id]" value="<?php echo $email->id ?>" /></p>
  </div>
</form>

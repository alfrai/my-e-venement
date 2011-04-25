<form action="<?php echo url_for('ticket/validate?id='.$transaction->id) ?>" method="get">
  <p><input type="submit" value="<?php echo __('Verify and validate') ?>" name="verify" /></p>
</form>

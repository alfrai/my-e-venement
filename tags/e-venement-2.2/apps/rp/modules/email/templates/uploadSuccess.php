<form action="<?php echo url_for('email/attach') ?>" method="post" enctype="multipart/form-data">
  <p><input type="file" name="attachment" /></p>
  <p><input type="submit" name="" value="Submit" /><input type="hidden" name="email[id]" value="<?php echo $email->id ?>" /></p>
</form>

<?php use_helper('I18N') ?>

<div class="ui-widget-header ui-corner-all fg-toolbar">
  <h2><?php echo __('Forgot your password?', null, 'sf_guard') ?></h2>
</div>

<p>
  <?php echo __('Do not worry, we can help you get back in to your account safely!') ?>
  <?php echo __('Fill out the form below to request an e-mail with information on how to reset your password.') ?>
</p>

<form action="<?php echo url_for('@sf_guard_forgot_password') ?>" method="post">
  <table>
    <tbody>
      <?php echo $form ?>
    </tbody>
    <tfoot><tr><td><input type="submit" name="change" value="<?php echo __('Request', null, 'sf_guard') ?>" /></td></tr></tfoot>
  </table>
</form>

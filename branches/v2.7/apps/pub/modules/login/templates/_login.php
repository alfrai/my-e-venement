<?php echo $form->renderHiddenFields() ?>
<?php foreach ( array('email','password') as $fieldname ): ?>
  <p class="<?php echo $fieldname ?>">
    <?php echo $form[$fieldname]->renderLabel() ?>
    <?php echo $form[$fieldname] ?>
    <span class="error"><?php if ( isset($errors[$fieldname]) ) echo __($errors[$fieldname]) ?></span>
  </p>
<?php endforeach ?>
  <p class="submit">
    <label></label>
    <input type="submit" value="Ok" name="submit" />
    <?php $vel = sfConfig::get('app_tickets_vel', array()) ?>
    <?php if (!( isset($vel['one_shot']) && $vel['one_shot'] )): ?>
    <a href="<?php echo url_for('login/forgot') ?>" class="forgot"><?php echo __('Send me a new password') ?></a>
    <?php endif ?>
  </p>
